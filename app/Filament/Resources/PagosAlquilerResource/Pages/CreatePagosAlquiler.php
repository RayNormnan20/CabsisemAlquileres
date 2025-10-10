<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use App\Models\LogActividad;
use App\Models\Alquiler;
use App\Models\PagoAlquiler;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CreatePagosAlquiler extends CreateRecord
{
    protected static string $resource = PagosAlquilerResource::class;

    protected static string $view = 'filament.pages.create-pago-alquiler';

    public function getView(): string
    {
        return static::$view;
    }

    public ?int $currentRutaId = null;
    public ?int $selectedAlquilerId = null;
    public array $pagosMensuales = [];
    public array $detallesPagos = [];
    public float $totalAbonos = 0;

    public function mount(): void
    {
        parent::mount();
        $this->currentRutaId = Session::get('selected_ruta_id');
    }

    protected function afterCreate(): void
    {
        // Registrar la actividad en el log
        LogActividad::registrar(
            'Pagos Alquiler',
            'Registró un nuevo pago de alquiler por S/. ' . number_format($this->record->monto_pagado, 2) . ' para el departamento ' . $this->record->alquiler->departamento->numero_departamento,
            [
                'pago_id' => $this->record->id_pago_alquiler,
                'alquiler_id' => $this->record->id_alquiler,
                'departamento_numero' => $this->record->alquiler->departamento->numero_departamento,
                'edificio_nombre' => $this->record->alquiler->departamento->edificio ? $this->record->alquiler->departamento->edificio->nombre : 'Sin Edificio',
                'inquilino_nombre' => $this->record->alquiler->inquilino->nombre_completo ?? 'Sin inquilino',
                'monto_pagado' => $this->record->monto_pagado,
                'fecha_pago' => $this->record->fecha_pago->format('Y-m-d'),
                'mes_correspondiente' => $this->record->mes_correspondiente,
                'ano_correspondiente' => $this->record->ano_correspondiente
            ]
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario que registra el pago automáticamente
        $data['id_usuario_registro'] = auth()->id();

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pago de alquiler registrado exitosamente';
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'El pago de alquiler ha sido registrado correctamente.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateAction(),
            $this->getCancelAction(),
        ];
    }

    protected function getCreateAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label('Crear')
            ->action('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCancelAction(): Actions\Action
    {
        return Actions\Action::make('cancel')
            ->label('Cancelar')
            ->url($this->getResource()::getUrl('index'))
            ->color('secondary');
    }


    public function updatedData($value, $key)
    {
        if ($key === 'id_alquiler' && $value) {
            $this->selectedAlquilerId = $value;
            $this->loadResumenData($value);
        }
    }

    // Método alternativo para Filament
    protected function afterStateUpdated($component, $state): void
    {
        if ($component === 'data.id_alquiler' && $state) {
            $this->selectedAlquilerId = $state;
            $this->loadResumenData($state);
        }
    }

    // Listeners para eventos de Livewire
    protected $listeners = ['alquilerChanged' => 'handleAlquilerChanged'];

    public function handleAlquilerChanged($alquilerId)
    {
        Log::info("HandleAlquilerChanged llamado con alquilerId: " . $alquilerId);
        Log::info("Tipo de alquilerId: " . gettype($alquilerId));
        Log::info("selectedAlquilerId antes en handleAlquilerChanged: " . $this->selectedAlquilerId);
        
        $this->selectedAlquilerId = (int) $alquilerId;
        Log::info("selectedAlquilerId después en handleAlquilerChanged: " . $this->selectedAlquilerId);
        
        $this->loadResumenData($alquilerId);
        
        Log::info("HandleAlquilerChanged completado");
    }

    public function loadResumenData($alquilerId)
    {
        Log::info("LoadResumenData llamado con alquilerId: " . $alquilerId);
        Log::info("selectedAlquilerId antes: " . $this->selectedAlquilerId);
        
        if (!$alquilerId) {
            $this->selectedAlquilerId = null;
            $this->pagosMensuales = [];
            $this->detallesPagos = [];
            $this->totalAbonos = 0;
            Log::info("AlquilerId vacío, limpiando datos");
            return;
        }

        // Asegurar que selectedAlquilerId se actualice
        $this->selectedAlquilerId = (int) $alquilerId;
        Log::info("selectedAlquilerId actualizado a: " . $this->selectedAlquilerId);

        $alquiler = Alquiler::with(['departamento.edificio', 'inquilino', 'pagos'])
            ->find($alquilerId);

        if (!$alquiler) {
            Log::error("No se encontró alquiler con ID: " . $alquilerId);
            return;
        }

        Log::info("Alquiler encontrado: " . $alquiler->id_alquiler . " - Departamento: " . $alquiler->departamento->numero_departamento);

        $this->loadPagosMensuales($alquiler);
        $this->loadDetallesPagos($alquiler);
        
        Log::info("Datos cargados - Pagos mensuales: " . count($this->pagosMensuales) . ", Detalles: " . count($this->detallesPagos) . ", Total abonos: " . $this->totalAbonos);
        Log::info("selectedAlquilerId final: " . $this->selectedAlquilerId);
        
        // Forzar actualización del componente
        $this->emit('refreshComponent');
    }

    private function loadPagosMensuales($alquiler)
    {
        $fechaInicio = Carbon::parse($alquiler->fecha_inicio);
        $fechaActual = Carbon::now();
        $fechaFin = $alquiler->fecha_fin ? Carbon::parse($alquiler->fecha_fin) : null;
        $pagosMensuales = [];
        $totalAbonos = 0;

        $fechaLimite = $fechaFin && $fechaFin->lt($fechaActual) ? $fechaFin : $fechaActual;
        $fechaInicio = $fechaInicio->copy()->startOfMonth();
        $fechaMes = $fechaInicio->copy();

        while ($fechaMes->lte($fechaLimite->startOfMonth())) {
            $mes = $fechaMes->format('Y-m');
            $nombreMes = $fechaMes->locale('es')->isoFormat('MMMM YYYY');

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
            } else {
                if ($fechaMes->lt(Carbon::now()->startOfMonth())) {
                    $estado = 'DEUDA PENDIENTE';
                }
            }

            $pagosMensuales[] = [
                'mes' => $nombreMes,
                'total' => $alquiler->precio_mensual,
                'pagado' => $abonosDelMes,
                'estado' => $estado
            ];

            $totalAbonos += $abonosDelMes;
            $fechaMes->addMonth();
        }

        $this->pagosMensuales = $pagosMensuales;
        $this->totalAbonos = $totalAbonos;
    }

    private function loadDetallesPagos($alquiler)
    {
        $pagos = PagoAlquiler::where('id_alquiler', $alquiler->id_alquiler)
            ->with(['usuarioRegistro'])
            ->orderBy('fecha_pago', 'desc')
            ->get();

        $detallesPagos = [];
        foreach ($pagos as $pago) {
            $detallesPagos[] = [
                'cliente_nombre' => $alquiler->inquilino->nombre_completo ?? 'N/A',
                'fecha_pago' => $pago->fecha_pago->format('Y-m-d'),
                'mes_correspondiente' => $pago->mes_correspondiente,
                'ano_correspondiente' => $pago->ano_correspondiente,
                'monto_pagado' => $pago->monto_pagado,
                'metodo_pago' => $pago->metodo_pago,
                'cobrador_nombre' => $pago->usuarioRegistro->name ?? 'N/A',
                'observaciones' => $pago->observaciones,
                'recibo_path' => $pago->recibo_path,
                'foto_1_path' => $pago->foto_1_path,
                'foto_2_path' => $pago->foto_2_path,
                'foto_3_path' => $pago->foto_3_path,
            ];
        }

        $this->detallesPagos = $detallesPagos;
    }
}