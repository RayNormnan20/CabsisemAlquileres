<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    use HasFactory;

    protected $table = 'oficina';
    protected $primaryKey = 'id_oficina';

    protected $fillable = [
        'nombre',
        'id_moneda',
        'pais',
        'codigo',
        'max_abonos_diarios', // Nuevo campo
        'porcentajes_credito',
        'activar_seguros' // Nuevo campo
    ];

    protected $casts = [
        'max_abonos_diarios' => 'integer',
        'activar_seguros' => 'boolean'
    ];

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'id_moneda', 'id_moneda');
    }


    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_oficina');
    }

}