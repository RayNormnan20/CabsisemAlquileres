<?php

namespace App\Events;

use App\Models\ClienteAlquiler;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClienteAlquilerCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clienteAlquiler;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ClienteAlquiler $clienteAlquiler)
    {
        $this->clienteAlquiler = $clienteAlquiler;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast tanto en el canal general como en el canal específico de la ruta
        $channels = [new Channel('clientes-alquiler')];
        
        // Si el cliente tiene una ruta asignada, también broadcast en el canal de esa ruta
        if ($this->clienteAlquiler->id_ruta) {
            $channels[] = new Channel('ruta.' . $this->clienteAlquiler->id_ruta);
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
        return 'cliente-alquiler.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'clienteAlquiler' => [
                'id' => $this->clienteAlquiler->id_cliente_alquiler,
                'nombre' => $this->clienteAlquiler->nombre,
                'apellido' => $this->clienteAlquiler->apellido,
                'numero_documento' => $this->clienteAlquiler->numero_documento,
                'celular' => $this->clienteAlquiler->celular,
                'direccion' => $this->clienteAlquiler->direccion,
                'id_ruta' => $this->clienteAlquiler->id_ruta,
                'activo' => $this->clienteAlquiler->activo,
            ],
            'message' => 'Nuevo cliente de alquiler creado',
            'timestamp' => now()->toISOString(),
        ];
    }
}