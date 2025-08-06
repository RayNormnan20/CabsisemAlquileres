<?php

namespace App\Filament\Resources\AbonosResource\Pages;

use App\Filament\Resources\AbonosResource;
use App\Filament\Resources\CreditosResource;
use App\Models\Creditos;
use Filament\Pages\Actions;
use App\Models\Abonos;
use App\Models\LogActividad;
use Filament\Resources\Pages\EditRecord;

class EditAbonos extends EditRecord
{
    protected static string $resource = AbonosResource::class;

    public ?string $metodo_pago = null;

    public function mount($record): void
    {
        parent::mount($record);
        
        // Si no hay referer o viene directamente del AbonosResource, limpiar sesión
        $referer = request()->header('referer');
        if (!$referer || !str_contains($referer, '/creditos/')) {
            session()->forget(['return_to_credito_view', 'credito_id_return']);
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $credito = Creditos::find($data['id_credito'] ?? null);

        if ($credito) {
            $data['fecha_credito'] = $credito->fecha_credito->format('d/m/Y');
            $data['fecha_vencimiento'] = $credito->fecha_vencimiento->format('d/m/Y');
            $data['valor_cuota'] = $credito->valor_cuota;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Verificar si viene desde el historial de abonos usando la sesión
        if (session('return_to_credito_view') && session('credito_id_return')) {
            $creditoId = session('credito_id_return');
            
            // Limpiar la sesión
            session()->forget(['return_to_credito_view', 'credito_id_return']);
            
            return CreditosResource::getUrl('view', ['record' => $creditoId]);
        }
        
        // Si no viene del historial, regresar a la vista normal de abonos con filtro de cliente
        return $this->getResource()::getUrl('index', [
            'cliente_id' => $this->record->id_cliente
        ]);
    }

    protected function afterSave(): void
    {
        $abono = $this->record; // abono actualizado
        $credito = Creditos::find($abono->id_credito);

        if ($credito) {
            // Recalcular correctamente usando los conceptos (si usas conceptosAbonos)
            $totalAbonado = Abonos::where('id_credito', $credito->id_credito)
                ->sum('monto_abono'); // Puedes ajustar esto si sumas conceptos

            // Actualizar el saldo actual
            $credito->saldo_actual = $credito->monto_total - $totalAbonado;
            $credito->save();

            // Actualizar el saldo_posterior del abono también
            $abono->saldo_posterior = $credito->saldo_actual;
            $abono->save();

            $clienteNombre = $abono->cliente?->nombre . ' ' . $abono->cliente?->apellido;
            $rutaNombre = $abono->ruta?->nombre ?? 'Ruta desconocida';

            LogActividad::registrar(
                'Abonos',
                "Actualizó un abono de la ruta {$rutaNombre} para el cliente {$clienteNombre} del día " . $abono->fecha_pago->format('d M Y') . " por S/" . number_format($abono->monto_abono, 2),
                [
                    'abono_id' => $abono->id_abono,
                    'cliente_id' => $abono->id_cliente,
                    'ruta_id' => $abono->id_ruta,
                    'fecha_pago' => $abono->fecha_pago->format('Y-m-d'),
                    'monto_abono' => $abono->monto_abono
                ]
            );
            
        }
    }
}
