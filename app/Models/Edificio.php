<?php

namespace App\Models;

use App\Events\EdificioCreated;
use App\Events\EdificioUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Edificio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edificios';
    protected $primaryKey = 'id_edificio';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'numero_pisos',
        'descripcion',
        'id_cliente_alquiler', // Propietario del edificio
        'id_ruta', // Nueva relación directa con ruta
        'activo',
        'id_usuario_creador'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'numero_pisos' => 'integer'
    ];

    // Relación con ClienteAlquiler (propietario)
    public function propietario()
    {
        return $this->belongsTo(ClienteAlquiler::class, 'id_cliente_alquiler', 'id_cliente_alquiler');
    }

    // Relación con Departamentos
    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'id_edificio', 'id_edificio');
    }

    // Relación con usuario creador
    public function creador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    // Scope para edificios activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Nueva relación con ruta
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta', 'id_ruta');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($edificio) {
            EdificioCreated::dispatch($edificio);
        });

        static::updated(function ($edificio) {
            EdificioUpdated::dispatch($edificio);
        });
    }
}