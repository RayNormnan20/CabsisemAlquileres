<?php

namespace App\Filament\Resources\ResumenAlquilerResource\Pages;

use App\Filament\Resources\ResumenAlquilerResource;
use App\Models\Alquiler;
use App\Models\PagoAlquiler;
use App\Models\Edificio;
use App\Models\Departamento;
use App\Models\LogActividad;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Actions;

class ListResumenAlquiler extends ListRecords
{
    protected static string $resource = ResumenAlquilerResource::class;
    protected static string $view = 'filament.pages.list-resumen-alquiler';

    public $selectedEdificio = null;
    public $selectedDepartamento = null;
    public $pagosMensuales = [];
    public $detallesPagos = [];
    public $totalAbonos = 0;

    protected function getTitle(): string
    {
        return 'Resumen de Alquiler';
    }

    protected function getSubheading(): ?string
    {
        return 'Selecciona un edificio y departamento para ver el resumen detallado';
    }

    public function mount(): void
    {
        parent::mount();
        $this->loadData();
    }

    public function updatedSelectedEdificio($value)
    {
        $this->selectedDepartamento = null;
        $this->loadData();
    }

    public function updatedSelectedDepartamento($value)
    {
        $this->loadData();
    }

    public function getEdificios()
    {
        return Edificio::orderBy('nombre')->pluck('nombre', 'id_edificio')->toArray();
    }

    public function getDepartamentos()
    {
        if (!$this->selectedEdificio) {
            return [];
        }

        return Departamento::where('id_edificio', $this->selectedEdificio)
            ->orderBy('numero_departamento')
            ->pluck('numero_departamento', 'id_departamento')
            ->toArray();
    }

    public function loadData()
    {
        if (!$this->selectedEdificio || !$this->selectedDepartamento) {
            $this->pagosMensuales = [];
            $this->detallesPagos = [];
            $this->totalAbonos = 0;
            return;
        }

        // Obtener el alquiler activo del departamento seleccionado
        $alquiler = Alquiler::where('id_departamento', $this->selectedDepartamento)
            ->where('estado_alquiler', 'activo')
            ->first();

        if (!$alquiler) {
            $this->pagosMensuales = [];
            $this->detallesPagos = [];
            $this->totalAbonos = 0;
            return;
        }

        $this->loadPagosMensuales($alquiler);
        $this->loadDetallesPagos($alquiler);
    }

    private function loadPagosMensuales($alquiler)
    {
        $fechaInicio = Carbon::parse($alquiler->fecha_inicio);
        $fechaActual = Carbon::now();
        $fechaFin = $alquiler->fecha_fin ? Carbon::parse($alquiler->fecha_fin) : null;
        $pagosMensuales = [];
        $totalAbonos = 0;

        // Determinar la fecha límite (fecha fin del alquiler o fecha actual, lo que sea menor)
        $fechaLimite = $fechaFin && $fechaFin->lt($fechaActual) ? $fechaFin : $fechaActual;

        // Mostrar desde el inicio del alquiler (sin limitar a 12 meses)
        // Mantener inicio de mes para una visualización uniforme
        $fechaInicio = $fechaInicio->copy()->startOfMonth();

        // Generar meses desde el inicio del alquiler hasta la fecha límite
        $fechaMes = $fechaInicio->copy();

        while ($fechaMes->lte($fechaLimite->startOfMonth())) {
            $mes = $fechaMes->format('Y-m');
            $nombreMes = $fechaMes->locale('es')->isoFormat('MMMM YYYY');

            // Calcular la suma de abonos para este mes específico
            $abonosDelMes = PagoAlquiler::where('id_alquiler', $alquiler->id_alquiler)
                ->where('mes_correspondiente', $fechaMes->month)
                ->where('ano_correspondiente', $fechaMes->year)
                ->sum('monto_pagado');

            $estado = 'PENDIENTE';
            if ($abonosDelMes > 0) {
                if ($abonosDelMes >= $alquiler->precio_mensual) {
                    $estado = 'CANCELADO';
                } else {
                    $estado = 'PAGO PARCIAL';
                }
                $totalAbonos += $abonosDelMes;
            } elseif ($fechaMes->lt(Carbon::now()->startOfMonth())) {
                $estado = 'DEUDA PENDIENTE';
            }

            $pagosMensuales[] = [
                'mes' => ucfirst($nombreMes),
                'total' => $alquiler->precio_mensual,
                'pagado' => $abonosDelMes,
                'estado' => $estado,
                'fecha' => $fechaMes->copy()
            ];

            $fechaMes->addMonth();
        }

        $this->pagosMensuales = $pagosMensuales;
        $this->totalAbonos = $totalAbonos;
    }

    private function loadDetallesPagos($alquiler)
    {
        $this->detallesPagos = PagoAlquiler::where('pagos_alquiler.id_alquiler', $alquiler->id_alquiler)
            ->join('alquileres', 'pagos_alquiler.id_alquiler', '=', 'alquileres.id_alquiler')
            ->join('clientes_alquiler', 'alquileres.id_cliente_alquiler', '=', 'clientes_alquiler.id_cliente_alquiler')
            ->leftJoin('users', 'pagos_alquiler.id_usuario_registro', '=', 'users.id')
            ->select(
                'pagos_alquiler.*',
                DB::raw('CONCAT(clientes_alquiler.nombre, " ", clientes_alquiler.apellido) as cliente_nombre'),
                'users.name as cobrador_nombre',
                'alquileres.imagen_1_path',
                'alquileres.imagen_2_path',
                'alquileres.imagen_3_path'
            )
            ->orderBy('pagos_alquiler.fecha_pago', 'desc')
            ->get()
            ->toArray();
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('exportarPDF')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->disabled(fn () => !$this->selectedEdificio || !$this->selectedDepartamento)
                ->action('exportToPDF'),
        ];
    }

    public function exportToPDF()
    {
        try {
            if (!$this->selectedEdificio || !$this->selectedDepartamento) {
                $this->notify('warning', 'Debe seleccionar un edificio y departamento primero');
                return;
            }

            // Obtener datos del edificio y departamento
            $edificio = Edificio::find($this->selectedEdificio);
            $departamento = Departamento::find($this->selectedDepartamento);

            if (!$edificio) {
                $this->notify('error', 'No se encontró el edificio seleccionado');
                return;
            }

            if (!$departamento) {
                $this->notify('error', 'No se encontró el departamento seleccionado');
                return;
            }

            // Obtener el alquiler activo
            $alquiler = Alquiler::where('id_departamento', $this->selectedDepartamento)
                ->where('estado_alquiler', 'activo')
                ->with(['inquilino', 'departamento.edificio'])
                ->first();

            if (!$alquiler) {
                $this->notify('warning', 'No se encontró un alquiler activo para este departamento');
                return;
            }

            $data = [
                'edificio' => $edificio,
                'departamento' => $departamento,
                'alquiler' => $alquiler,
                'pagosMensuales' => $this->pagosMensuales,
                'detallesPagos' => $this->detallesPagos,
                'totalAbonos' => $this->totalAbonos,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            ];

            $pdf = Pdf::loadView('filament.exports.resumen-alquiler-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'Arial',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                         'chroot' => public_path(),
                         'enable_php' => true,
                     ]);

            $fileName = 'resumen-alquiler-' . $edificio->nombre . '-depto-' . $departamento->numero_departamento . '-' . now()->format('Y-m-d') . '.pdf';

            // Registrar log de actividad para la descarga del PDF
            LogActividad::registrar(
                'Historial',
                "Descargó PDF del historial de alquiler del edificio {$edificio->nombre} - Departamento {$departamento->numero_departamento}",
                [
                    'edificio_id' => $edificio->id_edificio,
                    'edificio_nombre' => $edificio->nombre,
                    'departamento_id' => $departamento->id_departamento,
                    'departamento_numero' => $departamento->numero_departamento,
                    'alquiler_id' => $alquiler->id_alquiler,
                    'inquilino_nombre' => $alquiler->inquilino->nombre_completo ?? 'Sin cliente',
                    'archivo_generado' => $fileName,
                    'total_abonos' => $this->totalAbonos,
                    'cantidad_pagos' => count($this->detallesPagos)
                ]
            );

            return response()->streamDownload(
                fn () => print($pdf->stream()),
                $fileName,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            $this->notify('danger', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
}