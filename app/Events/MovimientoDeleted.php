<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MovimientoDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $movimiento;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($movimiento)
    {
        $this->movimiento = $movimiento;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
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
        return 'movimiento.deleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $movimientoArray = is_object($this->movimiento) && method_exists($this->movimiento, 'toArray') 
            ? $this->movimiento->toArray() 
            : (array) $this->movimiento;
            
        return [
            'movimiento' => $movimientoArray,
            'message' => 'Movimiento eliminado: ' . ($this->movimiento->concepto ?? 'Movimiento'),
            'timestamp' => now()->toISOString()
        ];
    }
}
