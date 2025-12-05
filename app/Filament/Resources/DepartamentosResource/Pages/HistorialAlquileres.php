<?php

namespace App\Filament\Resources\DepartamentosResource\Pages;

use App\Filament\Resources\DepartamentosResource;
use Filament\Resources\Pages\Page;
use App\Models\Departamento;
use App\Models\Alquiler;
use App\Models\PagoAlquiler;
use App\Models\EstadoDepartamento;

class HistorialAlquileres extends Page
{
    protected static string $resource = DepartamentosResource::class;
    protected static string $view = 'filament.pages.historial-alquiler-depto';

    public ?Departamento $departamento = null;
    public $alquileres = [];
    public $pagosCliente = [];
    public ?int $alquilerSeleccionadoId = null;
    public bool $tieneActivo = false;
    public bool $confirmandoFinalizacion = false;
    public ?int $alquilerAConfirmarId = null;
    public bool $visualizandoFotos = false;
    public array $fotosPago = [];

    public function mount($record): void
    {
        $this->departamento = Departamento::with('edificio')->findOrFail($record);
        $this->alquileres = Alquiler::where('id_departamento', $record)
            ->with('inquilino')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        $this->tieneActivo = Alquiler::where('id_departamento', $record)
            ->where('estado_alquiler', 'activo')
            ->exists();

        // Preseleccionar un alquiler si viene como parámetro en la URL
        $alquilerId = request()->query('alquilerId');
        if ($alquilerId) {
            // Verificar que el alquiler pertenezca a este departamento
            $alquilerPertenece = Alquiler::where('id_alquiler', $alquilerId)
                ->where('id_departamento', $record)
                ->exists();
            if ($alquilerPertenece) {
                $this->verPagos((int) $alquilerId);
            }
        }
    }

    public function verPagos(int $alquilerId): void
    {
        $this->alquilerSeleccionadoId = $alquilerId;
        $this->pagosCliente = PagoAlquiler::where('id_alquiler', $alquilerId)
            ->with('usuarioRegistro')
            ->orderBy('fecha_pago', 'desc')
            ->get()
            ->toArray();
    }

    public function abrirConfirmacion(int $alquilerId): void
    {
        $this->alquilerAConfirmarId = $alquilerId;
        $this->confirmandoFinalizacion = true;
    }

    public function cancelarConfirmacion(): void
    {
        $this->alquilerAConfirmarId = null;
        $this->confirmandoFinalizacion = false;
    }

    public function abrirFotosPago(int $pagoId): void
    {
        $pago = PagoAlquiler::findOrFail($pagoId);
        $urls = [];
        if ($pago->foto_1_path) { $urls[] = asset('storage/' . $pago->foto_1_path); }
        if ($pago->foto_2_path) { $urls[] = asset('storage/' . $pago->foto_2_path); }
        if ($pago->foto_3_path) { $urls[] = asset('storage/' . $pago->foto_3_path); }
        $this->fotosPago = $urls;
        $this->visualizandoFotos = !empty($urls);
    }

    public function cerrarFotosPago(): void
    {
        $this->visualizandoFotos = false;
        $this->fotosPago = [];
    }

    public function finalizarAlquiler(int $alquilerId): void
    {
        $this->confirmandoFinalizacion = false;
        $this->alquilerAConfirmarId = null;
        $alquiler = Alquiler::with('departamento')->findOrFail($alquilerId);
        $alquiler->estado_alquiler = 'finalizado';
        $alquiler->save();

        $fechaInicio = \Carbon\Carbon::parse($alquiler->fecha_inicio);
        $hoy = \Carbon\Carbon::today();
        if ($fechaInicio->isSameDay($hoy)) {
            $alquiler->update([
                'fecha_proximo_pago' => $fechaInicio->copy()->addMonth()
            ]);
        }

        $estadoDisponible = EstadoDepartamento::where('nombre', 'Disponible')
            ->where('activo', true)
            ->first();
        if ($estadoDisponible && $alquiler->departamento) {
            $departamento = $alquiler->departamento;
            $departamento->id_estado_departamento = $estadoDisponible->id_estado_departamento;
            $departamento->save();
        }

        $this->alquileres = Alquiler::where('id_departamento', $this->departamento->id_departamento)
            ->with('inquilino')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        $this->tieneActivo = Alquiler::where('id_departamento', $this->departamento->id_departamento)
            ->where('estado_alquiler', 'activo')
            ->exists();
    }

    protected function getTitle(): string
    {
        return 'Historial de Alquiler';
    }
}
