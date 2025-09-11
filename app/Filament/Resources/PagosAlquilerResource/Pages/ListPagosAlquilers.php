<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use App\Filament\Widgets\PagosAlquilerWebSocketWidget;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagosAlquilers extends ListRecords
{
    protected static string $resource = PagosAlquilerResource::class;
    
    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        'refreshPagosAlquilerTable' => '$refresh',
        '$refresh',
    ];
    
    protected function getHeaderWidgets(): array
    {
        return [
            PagosAlquilerWebSocketWidget::class,
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

