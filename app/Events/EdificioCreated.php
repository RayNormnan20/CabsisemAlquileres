<?php

namespace App\Events;

use App\Models\Edificio;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EdificioCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $edificio;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Edificio $edificio)
    {
        $this->edificio = $edificio;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('edificios');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'edificio.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'edificio' => [
                'id' => $this->edificio->id_edificio,
                'nombre' => $this->edificio->nombre,
                'direccion' => $this->edificio->direccion,
                'numero_pisos' => $this->edificio->numero_pisos,
                'id_cliente_alquiler' => $this->edificio->id_cliente_alquiler,
                'activo' => $this->edificio->activo,
            ],
            'message' => 'Nuevo edificio creado',
            'timestamp' => now()->toISOString(),
        ];
    }
}