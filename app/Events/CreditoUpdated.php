<?php

namespace App\Events;

use App\Models\Creditos;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditoUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $credito;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Creditos $credito)
    {
        $this->credito = $credito;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('ruta.' . $this->credito->id_ruta);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'credito.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'credito' => [
                'id' => $this->credito->id_credito,
                'id_cliente' => $this->credito->id_cliente,
                'id_ruta' => $this->credito->id_ruta,
                'valor_credito' => $this->credito->valor_credito,
                'saldo_actual' => $this->credito->saldo_actual,
                'fecha_credito' => $this->credito->fecha_credito,
                'cliente' => $this->credito->cliente ? [
                    'nombre' => $this->credito->cliente->nombre,
                    'apellido' => $this->credito->cliente->apellido,
                ] : null,
            ],
            'message' => 'Crédito actualizado',
            'timestamp' => now()->toISOString(),
        ];
    }
}