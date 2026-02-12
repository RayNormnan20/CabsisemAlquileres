<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditoDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $id_ruta;
    public $creditoData;

    /**
     * Create a new event instance.
     *
     * @param int $id_ruta
     * @param array $creditoData
     * @return void
     */
    public function __construct($id_ruta, $creditoData)
    {
        $this->id_ruta = $id_ruta;
        $this->creditoData = $creditoData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel('ruta.' . $this->id_ruta),
            new Channel('movimientos'),
            new Channel('ingresos-gastos')
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'credito.deleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'credito' => $this->creditoData,
            'message' => 'Crédito eliminado',
            'timestamp' => now()->toISOString(),
        ];
    }
}
