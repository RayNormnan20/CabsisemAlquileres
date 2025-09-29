<?php

namespace App\Http\Livewire;

use App\Models\Creditos;
use LivewireUI\Modal\ModalComponent;

class ModalComprobantes extends ModalComponent
{
    public $creditoId;
    public $credito;
    public $comprobantes;

    public function mount($creditoId)
    {
        $this->creditoId = $creditoId;
        $this->credito = Creditos::with(['conceptosCredito' => function($query) {
            $query->whereNotNull('foto_comprobante');
        }])->find($creditoId);
        
        $this->comprobantes = $this->credito ? $this->credito->conceptosCredito : collect();
    }

    public static function modalMaxWidth(): string
    {
        return '4xl';
    }

    public function cerrarYRedirigir()
    {
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modal-comprobantes');
    }
}