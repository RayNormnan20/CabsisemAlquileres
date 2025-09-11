<?php

namespace App\Events;

use App\Models\Departamento;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepartamentoUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $departamento;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Departamento $departamento)
    {
        $this->departamento = $departamento;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast tanto en el canal general como en el canal específico de la ruta
        $channels = [new Channel('departamentos')];
        
        // Si el departamento tiene una ruta asignada, también broadcast en el canal de esa ruta
        if ($this->departamento->id_ruta) {
            $channels[] = new Channel('ruta.' . $this->departamento->id_ruta);
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
        return 'departamento.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'departamento' => [
                'id' => $this->departamento->id_departamento,
                'id_edificio' => $this->departamento->id_edificio,
                'numero_departamento' => $this->departamento->numero_departamento,
                'piso' => $this->departamento->piso,
                'cuartos' => $this->departamento->cuartos,
                'banos' => $this->departamento->banos,
                'metros_cuadrados' => $this->departamento->metros_cuadrados,
                'precio_alquiler' => $this->departamento->precio_alquiler,
                'id_estado_departamento' => $this->departamento->id_estado_departamento,
                'id_ruta' => $this->departamento->id_ruta,
                'activo' => $this->departamento->activo,
            ],
            'message' => 'Departamento actualizado',
            'timestamp' => now()->toISOString(),
        ];
    }
}