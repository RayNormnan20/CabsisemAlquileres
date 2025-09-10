<?php

namespace App\Events;

use App\Models\Clientes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClienteCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cliente;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Clientes $cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('clientes');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'cliente.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'cliente' => [
                'id' => $this->cliente->id_cliente,
                'nombre' => $this->cliente->nombre,
                'apellido' => $this->cliente->apellido,
                'numero_documento' => $this->cliente->numero_documento,
                'celular' => $this->cliente->celular,
                'direccion' => $this->cliente->direccion,
                'id_ruta' => $this->cliente->id_ruta,
                'activo' => $this->cliente->activo,
            ],
            'message' => 'Nuevo cliente creado',
            'timestamp' => now()->toISOString(),
        ];
    }
}