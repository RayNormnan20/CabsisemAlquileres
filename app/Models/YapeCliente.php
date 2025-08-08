<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class YapeCliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'yape_clientes';

    protected $fillable = [
        'id_cliente', // Asegúrate que coincida con el nombre en la migración
        'nombre',
        'user_id',
        'monto',
        'entregar',
    ];

    // Agregar este método para permitir que id_cliente sea null
    protected $attributes = [
        'id_cliente' => null,
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function abonos()
    {
        return $this->hasMany(Abonos::class, 'id_yape_cliente', 'id');
    }
}