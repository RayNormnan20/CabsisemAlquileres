<?php

namespace App\Events;

use App\Models\PagoAlquiler;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagoAlquilerUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pagoAlquiler;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PagoAlquiler $pagoAlquiler)
    {
        $this->pagoAlquiler = $pagoAlquiler;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('pagos-alquiler');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'pago-alquiler.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'pagoAlquiler' => [
                'id' => $this->pagoAlquiler->id_pago_alquiler,
                'id_alquiler' => $this->pagoAlquiler->id_alquiler,
                'monto_pago' => $this->pagoAlquiler->monto_pago,
                'fecha_pago' => $this->pagoAlquiler->fecha_pago,
                'metodo_pago' => $this->pagoAlquiler->metodo_pago,
                'observaciones' => $this->pagoAlquiler->observaciones,
            ],
            'message' => 'Pago de alquiler actualizado',
            'timestamp' => now()->toISOString(),
        ];
    }
}