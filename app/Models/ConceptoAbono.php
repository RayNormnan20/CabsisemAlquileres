<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConceptoAbono extends Model
{
    protected $table = 'conceptos_abono';
    protected $primaryKey = 'id_concepto_abono';

    protected $fillable = [
        'id_abono',
        'id_usuario',
        'tipo_concepto',
        'monto',
        'foto_comprobante',
        'referencia',
        'id_caja'
    ];

    public function abono()
    {
        return $this->belongsTo(Abonos::class, 'id_abono');
    }


     public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
/*
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
        */
}