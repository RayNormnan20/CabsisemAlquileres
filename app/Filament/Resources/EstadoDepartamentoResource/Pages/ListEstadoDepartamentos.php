<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use App\Http\Livewire\Traits\RouteValidation;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstadoDepartamentos extends ListRecords
{
    use RouteValidation;
    protected static string $resource = EstadoDepartamentoResource::class;

    public function mount(): void
    {
        $this->validateAndCorrectSelectedRoute();
        parent::mount();
    }

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

