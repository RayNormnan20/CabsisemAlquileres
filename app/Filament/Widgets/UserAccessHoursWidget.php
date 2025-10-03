<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UserAccessHoursWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('Manage general settings');
    }

    protected function getTableQuery(): Builder
    {
        return User::query()->select(['id', 'name', 'email', 'access_start_hour', 'access_end_hour']);
    }

    protected function getTableHeading(): ?string
    {
        return 'Horarios de acceso por usuario';
    }

    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 10;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Usuario')->searchable()->sortable(),
           // TextColumn::make('email')->label('Email')->searchable(),
            BadgeColumn::make('access_start_hour')
                ->label('Inicio')
                ->colors(['secondary'])
                ->formatStateUsing(function ($state) {
                    return is_null($state) ? 'Sin restricción' : sprintf('%02d:00', (int) $state);
                }),
            BadgeColumn::make('access_end_hour')
                ->label('Fin')
                ->colors(['secondary'])
                ->formatStateUsing(function ($state) {
                    return is_null($state) ? 'Sin restricción' : sprintf('%02d:00', (int) $state);
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('configurar')
                ->label('Configurar horario')
                ->modalHeading('Configurar horario de acceso')
                ->form([
                    \Filament\Forms\Components\Select::make('access_start_hour')
                        ->label('Hora de inicio')
                        ->options($this->hourOptions())
                        ->placeholder('Sin restricción')
                        ->default(fn($record) => $record->access_start_hour)
                        ->nullable(),
                    \Filament\Forms\Components\Select::make('access_end_hour')
                        ->label('Hora de fin')
                        ->options($this->hourOptions())
                        ->placeholder('Sin restricción')
                        ->default(fn($record) => $record->access_end_hour)
                        ->nullable(),
                ])
                ->action(function (User $record, array $data) {
                    $record->access_start_hour = $data['access_start_hour'] ?? null;
                    $record->access_end_hour = $data['access_end_hour'] ?? null;
                    $record->save();
                    Notification::make()
                        ->title('Horario actualizado para ' . $record->name)
                        ->success()
                        ->send();
                })
                ->visible(fn() => auth()->user()->can('Manage general settings')),
            Action::make('limpiar')
                ->label('Quitar restricción')
                ->requiresConfirmation()
                ->color('danger')
                ->action(function (User $record) {
                    $record->access_start_hour = null;
                    $record->access_end_hour = null;
                    $record->save();
                    Notification::make()
                        ->title('Restricciones eliminadas para ' . $record->name)
                        ->success()
                        ->send();
                })
                ->visible(fn() => auth()->user()->can('Manage general settings')),
        ];
    }

    private function hourOptions(): array
    {
        $options = ['' => 'Sin restricción'];
        for ($h = 0; $h <= 23; $h++) {
            $options[(string)$h] = sprintf('%02d:00', $h);
        }
        return $options;
    }
}
