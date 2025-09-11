<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstadoDepartamentos extends ListRecords
{
    protected static string $resource = EstadoDepartamentoResource::class;

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'refreshEstadoDepartamentoTable' => '$refresh',
        '$refresh'
    ];

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\EstadoDepartamentoWebSocketWidget::class,
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

}

