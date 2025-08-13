<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departamentos';
    protected $primaryKey = 'id_departamento';

    protected $fillable = [
        'id_edificio',
        'numero_departamento',
        'piso',
        'cuartos',
        'banos',
        'metros_cuadrados',
        'precio_alquiler',
        'descripcion',
        'foto_path',
        'id_estado_departamento',
        'id_ruta', // Nuevo campo
        'activo',
        'id_usuario_creador'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'cuartos' => 'integer',
        'banos' => 'integer',
        'metros_cuadrados' => 'decimal:2',
        'precio_alquiler' => 'decimal:2'
    ];

    // Relación con Edificio
    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio', 'id_edificio');
    }

    // Relación con EstadoDepartamento
    public function estado()
    {
        return $this->belongsTo(EstadoDepartamento::class, 'id_estado_departamento', 'id_estado_departamento');
    }

    // Relación con Ruta (agregar después de la relación con estado)
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta', 'id_ruta');
    }

    // Relación con Alquileres
    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'id_departamento', 'id_departamento');
    }

    // Relación con usuario creador
    public function creador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    // Accessor para URL de foto
    public function getFotoUrlAttribute()
    {
        return $this->foto_path ? asset('storage/' . $this->foto_path) : null;
    }

    // Scope para departamentos disponibles
    public function scopeDisponibles($query)
    {
        return $query->whereHas('estado', function($q) {
            $q->where('nombre', 'Disponible');
        });
    }

    // Scope para departamentos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}