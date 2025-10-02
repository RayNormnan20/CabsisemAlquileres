<?php

namespace App\Models;

use App\Events\PagoAlquilerCreated;
use App\Events\PagoAlquilerUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoAlquiler extends Model
{
    use HasFactory;

    protected $table = 'pagos_alquiler';
    protected $primaryKey = 'id_pago_alquiler';

    protected $fillable = [
        'id_alquiler',
        'fecha_pago',
        'monto_pagado',
        'mes_correspondiente',
        'ano_correspondiente',
        'metodo_pago', // efectivo, transferencia, cheque
        'referencia_pago',
        'observaciones',
        'recibo_path',
        'foto_1_path',
        'foto_2_path',
        'foto_3_path',
        'id_usuario_registro'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto_pagado' => 'decimal:2',
        'mes_correspondiente' => 'integer',
        'ano_correspondiente' => 'integer'
    ];

    // Relación con Alquiler
    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler', 'id_alquiler');
    }

    // Relación con usuario que registró el pago
    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'id_usuario_registro');
    }

    // Accessor para URL del recibo
    public function getReciboUrlAttribute()
    {
        return $this->recibo_path ? asset('storage/' . $this->recibo_path) : null;
    }

    // Accessors para URLs de las fotos
    public function getFoto1UrlAttribute()
    {
        return $this->foto_1_path ? asset('storage/' . $this->foto_1_path) : null;
    }

    public function getFoto2UrlAttribute()
    {
        return $this->foto_2_path ? asset('storage/' . $this->foto_2_path) : null;
    }

    public function getFoto3UrlAttribute()
    {
        return $this->foto_3_path ? asset('storage/' . $this->foto_3_path) : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($pagoAlquiler) {
            PagoAlquilerCreated::dispatch($pagoAlquiler);
        });

        static::updated(function ($pagoAlquiler) {
            PagoAlquilerUpdated::dispatch($pagoAlquiler);
        });
    }
}