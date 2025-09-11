<?php

namespace App\Events;

use App\Models\ConceptoAbono;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConceptoAbonoCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conceptoAbono;

    public function __construct(ConceptoAbono $conceptoAbono)
    {
        $this->conceptoAbono = $conceptoAbono;
    }

    public function broadcastOn()
    {
        return [
            new Channel('conceptos-abonos'),
            new Channel('conceptos')
        ];
    }

    public function broadcastAs()
    {
        return 'concepto-abono.created';
    }

    public function broadcastWith()
    {
        return [
            'conceptoAbono' => $this->conceptoAbono,
            'message' => 'Nuevo concepto de abono creado',
            'timestamp' => now()->toISOString()
        ];
    }
}