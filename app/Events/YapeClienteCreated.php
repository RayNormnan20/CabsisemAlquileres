<?php

namespace App\Events;

use App\Models\YapeCliente;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class YapeClienteCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $yapeCliente;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(YapeCliente $yapeCliente)
    {
        $this->yapeCliente = $yapeCliente;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast en el canal general de yape-clientes
        $channels = [new Channel('yape-clientes')];
        
        // Si el yape cliente tiene una ruta asignada (a través del cliente), también broadcast en el canal de esa ruta
        if ($this->yapeCliente->cliente && $this->yapeCliente->cliente->id_ruta) {
            $channels[] = new Channel('ruta.' . $this->yapeCliente->cliente->id_ruta);
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'yape-cliente.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'yapeCliente' => [
                'id' => $this->yapeCliente->id,
                'nombre' => $this->yapeCliente->nombre,
                'monto' => $this->yapeCliente->monto,
                'id_cliente' => $this->yapeCliente->id_cliente,
                'cliente_nombre' => $this->yapeCliente->cliente ? $this->yapeCliente->cliente->nombre : null,
                'created_at' => $this->yapeCliente->created_at,
                'updated_at' => $this->yapeCliente->updated_at,
            ],
            'message' => 'Nuevo Yape Cliente registrado',
            'timestamp' => now()->toISOString(),
        ];
    }
}