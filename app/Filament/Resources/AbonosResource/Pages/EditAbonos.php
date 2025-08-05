<?php

namespace App\Filament\Resources\AbonosResource\Pages;

use App\Filament\Resources\AbonosResource;
use App\Models\Creditos;
use Filament\Pages\Actions;
    use App\Models\Abonos;
use App\Models\LogActividad;
use Filament\Resources\Pages\EditRecord;

class EditAbonos extends EditRecord
{
    protected static string $resource = AbonosResource::class;

    public ?string $metodo_pago = null;

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
        return $this->getResource()::getUrl('index');
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
