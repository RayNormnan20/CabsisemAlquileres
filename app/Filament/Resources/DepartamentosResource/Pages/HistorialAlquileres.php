<?php

namespace App\Filament\Resources\DepartamentosResource\Pages;

use App\Filament\Resources\DepartamentosResource;
use Filament\Resources\Pages\Page;
use App\Models\Departamento;
use App\Models\Alquiler;

class HistorialAlquileres extends Page
{
    protected static string $resource = DepartamentosResource::class;
    protected static string $view = 'filament.pages.historial-alquiler-depto';

    public ?Departamento $departamento = null;
    public $alquileres = [];

    public function mount($record): void
    {
        $this->departamento = Departamento::with('edificio')->findOrFail($record);
        $this->alquileres = Alquiler::where('id_departamento', $record)
            ->with('inquilino')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    protected function getTitle(): string
    {
        return 'Historial de Alquiler';
    }
}