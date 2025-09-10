<?php

namespace App\Events;

use App\Models\Abonos;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbonoUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $abono;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Abonos $abono)
    {
        $this->abono = $abono;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('ruta.' . $this->abono->id_ruta);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'abono.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'abono' => [
                'id' => $this->abono->id_abono,
                'id_credito' => $this->abono->id_credito,
                'id_cliente' => $this->abono->id_cliente,
                'id_ruta' => $this->abono->id_ruta,
                'monto_abono' => $this->abono->monto_abono,
                'saldo_anterior' => $this->abono->saldo_anterior,
                'saldo_posterior' => $this->abono->saldo_posterior,
                'fecha_pago' => $this->abono->fecha_pago,
                'credito' => $this->abono->credito ? [
                    'id' => $this->abono->credito->id_credito,
                    'valor_credito' => $this->abono->credito->valor_credito,
                ] : null,
                'cliente' => $this->abono->cliente ? [
                    'nombre' => $this->abono->cliente->nombre,
                    'apellido' => $this->abono->cliente->apellido,
                ] : null,
            ],
            'message' => 'Abono actualizado',
            'timestamp' => now()->toISOString(),
        ];
    }
}