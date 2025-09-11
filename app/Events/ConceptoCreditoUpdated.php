<?php

namespace App\Events;

use App\Models\ConceptoCredito;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConceptoCreditoUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conceptoCredito;

    public function __construct(ConceptoCredito $conceptoCredito)
    {
        $this->conceptoCredito = $conceptoCredito;
    }

    public function broadcastOn()
    {
        return [
            new Channel('conceptos-creditos'),
            new Channel('conceptos')
        ];
    }

    public function broadcastAs()
    {
        return 'concepto-credito.updated';
    }

    public function broadcastWith()
    {
        return [
            'conceptoCredito' => $this->conceptoCredito,
            'message' => 'Concepto de crédito actualizado',
            'timestamp' => now()->toISOString()
        ];
    }
}