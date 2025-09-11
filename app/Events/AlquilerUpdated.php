<?php

namespace App\Events;

use App\Models\Alquiler;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlquilerUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $alquiler;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Alquiler $alquiler)
    {
        $this->alquiler = $alquiler;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('alquileres');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'alquiler.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'alquiler' => [
                'id' => $this->alquiler->id_alquiler,
                'id_departamento' => $this->alquiler->id_departamento,
                'id_inquilino' => $this->alquiler->id_inquilino,
                'fecha_inicio' => $this->alquiler->fecha_inicio,
                'fecha_fin' => $this->alquiler->fecha_fin,
                'monto_alquiler' => $this->alquiler->monto_alquiler,
                'deposito_garantia' => $this->alquiler->deposito_garantia,
                'estado_alquiler' => $this->alquiler->estado_alquiler,
            ],
            'message' => 'Alquiler actualizado',
            'timestamp' => now()->toISOString(),
        ];
    }
}