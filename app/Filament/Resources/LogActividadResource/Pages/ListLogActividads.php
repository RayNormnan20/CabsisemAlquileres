<?php

namespace App\Filament\Resources\LogActividadResource\Pages;

use App\Filament\Resources\LogActividadResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogActividads extends ListRecords
{
    protected static string $resource = LogActividadResource::class;

    protected function getActions(): array
    {
        return [
          //  Actions\CreateAction::make(),
        ];
    }

    // Add this method to show all records by default
    protected function getTableRecordsPerPageSelectOptions(): array 
    {
        return [10, 25, 50, 100, 'all'];
    }

    // Fix: Return a large integer instead of 'all' string
    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 200; // Use 25 by default for faster loads
    }
}
