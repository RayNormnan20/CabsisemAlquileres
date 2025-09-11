<?php

namespace App\Events;

use App\Models\PlanillaRecaudador;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlanillaRecaudadorUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $planillaRecaudador;

    public function __construct($planillaRecaudador)
    {
        $this->planillaRecaudador = $planillaRecaudador;
    }

    public function broadcastOn()
    {
        return [
            new Channel('planilla-recaudador'),
            new Channel('planillas')
        ];
    }

    public function broadcastAs()
    {
        return 'planilla-recaudador.updated';
    }

    public function broadcastWith()
    {
        return [
            'planillaRecaudador' => $this->planillaRecaudador,
            'message' => 'Entrada en planilla recaudador actualizada',
            'timestamp' => now()->toISOString()
        ];
    }
}