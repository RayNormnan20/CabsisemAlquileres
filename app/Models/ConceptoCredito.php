<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\ConceptoCreditoCreated;
use App\Events\ConceptoCreditoUpdated;

class ConceptoCredito extends Model
{
    protected $table = 'conceptos_credito';
    protected $primaryKey = 'id_concepto_credito';

    protected $fillable = [
        'id_credito',
        'tipo_concepto',
        'monto',
        'foto_comprobante',
        'id_credito_anterior',
        'id_caja',
    ];

    public function credito()
    {
        return $this->belongsTo(Creditos::class, 'id_credito');
    }

    public function creditoAnterior()
    {
        return $this->belongsTo(Creditos::class, 'id_credito_anterior');
    }
/*
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
        */

    protected static function boot()
    {
        parent::boot();

        static::created(function ($conceptoCredito) {
            // Disparar evento WebSocket cuando se crea un concepto de crédito
            ConceptoCreditoCreated::dispatch($conceptoCredito);
        });

        static::updated(function ($conceptoCredito) {
            // Disparar evento WebSocket cuando se actualiza un concepto de crédito
            ConceptoCreditoUpdated::dispatch($conceptoCredito);
        });
    }
}