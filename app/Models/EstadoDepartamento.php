<?php

namespace App\Models;

use App\Events\EstadoDepartamentoCreated;
use App\Events\EstadoDepartamentoUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoDepartamento extends Model
{
    use HasFactory;

    protected $table = 'estados_departamento';
    protected $primaryKey = 'id_estado_departamento';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color', // Para mostrar en la interfaz
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación con Departamentos
    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'id_estado_departamento', 'id_estado_departamento');
    }

    // Scope para estados activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Cuando se crea un estado de departamento
        static::created(function ($estadoDepartamento) {
            event(new EstadoDepartamentoCreated($estadoDepartamento));
        });

        // Cuando se actualiza un estado de departamento
        static::updated(function ($estadoDepartamento) {
            event(new EstadoDepartamentoUpdated($estadoDepartamento));
        });
    }
}