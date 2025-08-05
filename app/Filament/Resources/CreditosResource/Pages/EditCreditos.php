<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCreditos extends EditRecord
{
    protected static string $resource = CreditosResource::class;

    protected function afterSave(): void
    {
        $clienteNombre = $this->record->cliente?->nombre . ' ' . $this->record->cliente?->apellido;
        $rutaNombre = $this->record->cliente?->ruta?->nombre ?? 'Ruta desconocida';

        $changes = $this->record->getChanges();
        $relevantChanges = [];
        $fieldsToLog = ['fecha_credito', 'valor_credito', 'porcentaje_interes'];

        foreach ($fieldsToLog as $field) {
            if (array_key_exists($field, $changes)) {
                if ($field === 'fecha_credito') {
                    $relevantChanges[$field] = $this->record->$field->format('Y-m-d');
                } else {
                    $relevantChanges[$field] = $changes[$field];
                }
            }
        }

        LogActividad::registrar(
            'Créditos',
            "Editó el crédito de {$clienteNombre} de la ruta {$rutaNombre}",
            $relevantChanges
        );

        Notification::make()
            ->title('Crédito actualizado exitosamente')
            ->success()
            ->send();
    }

   protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->abonos()->exists()) {
                        Notification::make()
                            ->title('No se puede eliminar el crédito')
                            ->body('Este crédito tiene abonos realizados y no puede ser eliminado.')
                            ->danger()
                            ->send();
                        throw new \Exception('El crédito tiene abonos realizados.');
                    }

                    $clienteNombre = $this->record->cliente?->nombre . ' ' . $this->record->cliente?->apellido;
                    $rutaNombre = $this->record->cliente?->ruta?->nombre ?? 'Ruta desconocida';

                    LogActividad::registrar(
                        'Créditos',
                        "Eliminó el crédito de {$clienteNombre} de la ruta {$rutaNombre}",
                        [
                            'credito_id' => $this->record->id_credito,
                            'cliente_id' => $this->record->id_cliente,
                            'datos_eliminados' => $this->record->toArray(),
                        ]
                    );
                })
                ->after(function () {
                    Notification::make()
                        ->title('Crédito eliminado exitosamente')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}