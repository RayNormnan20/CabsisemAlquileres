<?php

namespace App\Events;

use App\Models\EstadoDepartamento;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstadoDepartamentoUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estadoDepartamento;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(EstadoDepartamento $estadoDepartamento)
    {
        $this->estadoDepartamento = $estadoDepartamento;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('estados-departamento');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'estado-departamento.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'estadoDepartamento' => [
                'id' => $this->estadoDepartamento->id_estado_departamento,
                'nombre' => $this->estadoDepartamento->nombre,
                'descripcion' => $this->estadoDepartamento->descripcion,
                'color' => $this->estadoDepartamento->color,
                'activo' => $this->estadoDepartamento->activo,
            ],
            'message' => 'Estado de departamento actualizado',
            'timestamp' => now()->toISOString(),
        ];
    }
}