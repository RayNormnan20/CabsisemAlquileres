<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;

use App\Filament\Resources\CreditosResource\Widgets\HistorialAbonosClienteWidget;
use App\Models\Clientes;
use Filament\Resources\Pages\Page;

class ViewHistorialCliente extends Page
{
    protected static string $resource = CreditosResource::class;

    protected static string $view = 'filament.resources.creditos-resource.pages.view-historial-cliente';

    public Clientes $record;

    public function mount(int|string $cliente): void
    {
        $this->record = Clientes::findOrFail($cliente);
    }

    public function getTitle(): string
    {
        return "";
    }



    protected function getHeaderWidgets(): array
    {
        return [
            HistorialAbonosClienteWidget::class,
        ];
    }

    protected function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}