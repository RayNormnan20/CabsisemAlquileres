<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanillaRecaudador extends Model
{
protected $table = 'vista_planillas_recaudador';
protected $primaryKey = 'id_unico';
public $incrementing = false;
public $timestamps = false;

protected $casts = [
'valor_credito' => 'decimal:2',
'saldo_actual' => 'decimal:2',
'valor_cuota' => 'decimal:2',
'ultimo_monto_pagado' => 'decimal:2',
'fecha_credito' => 'date',
'fecha_proximo_pago' => 'date',
'ultima_fecha_pago' => 'datetime'
];

public function getKey()
{
return (string) $this->getAttribute($this->primaryKey);
}

public function ruta()
{
// Ahora sí existe id_ruta en la vista
return $this->belongsTo(Ruta::class, 'id_ruta', 'id_ruta');
}

// Relación con el crédito para acceder al campo por_renovar
public function credito()
{
    return $this->belongsTo(Creditos::class, 'id_credito', 'id_credito');
}
}
