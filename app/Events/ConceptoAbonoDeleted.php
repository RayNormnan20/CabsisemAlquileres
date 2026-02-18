<?php

namespace App\Events;

use App\Models\ConceptoAbono;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConceptoAbonoDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conceptoAbono;

    public function __construct($conceptoAbono)
    {
        $this->conceptoAbono = $conceptoAbono;
    }

    public function broadcastOn()
    {
        return [
            new Channel('conceptos-abonos'),
            new Channel('conceptos'),
        ];
    }

    public function broadcastAs()
    {
        return 'concepto-abono.deleted';
    }

    public function broadcastWith()
    {
        $data = $this->conceptoAbono;
        if ($this->conceptoAbono instanceof ConceptoAbono) {
            $data = $this->conceptoAbono->toArray();
        }

        return [
            'conceptoAbono' => $data,
            'message' => 'Concepto de abono eliminado',
            'timestamp' => now()->toISOString(),
        ];
    }
}
