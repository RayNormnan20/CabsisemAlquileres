<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'ruta';
    protected $primaryKey = 'id_ruta';

    protected $fillable = [
        'nombre',
        'codigo',
        'id_oficina',
        'id_usuario',
        'creada_en',
        'activa',
        'id_tipo_documento',
        'id_tipo_cobro',
        'agregar_ceros_cantidades',
        'porcentajes_credito'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'agregar_ceros_cantidades' => 'boolean',
        'creada_en' => 'date'
    ];

    // Relación con Oficina
    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'id_oficina', 'id_oficina');
    }


// Agrega esta relación al modelo
public function usuarios()
{
    return $this->belongsToMany(User::class, 'usuario_ruta', 'id_ruta', 'user_id');
}


    // Relación con TipoDocumento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento', 'id_tipo_documento');
    }

    // Relación con TipoCobro
    public function tipoCobro()
    {
        return $this->belongsTo(TipoCobro::class, 'id_tipo_cobro', 'id_tipo_cobro');
    }

    /**
     * Relación con los clientes a través de los créditos
     * Obtiene todos los clientes que tienen créditos en esta ruta
     */
    public function clientes()
    {
        return $this->hasManyThrough(
            Clientes::class,
            Creditos::class,
            'id_ruta', // Foreign key on Creditos table
            'id_cliente', // Foreign key on Clientes table
            'id_ruta', // Local key on Ruta table
            'id_cliente' // Local key on Creditos table
        )->distinct();
    }

    /**
     * Relación directa con los créditos de esta ruta
     */
    public function creditos()
    {
        return $this->hasMany(Creditos::class, 'id_ruta');
    }

    /**
     * Relación con abonos a través de créditos
     */
    public function abonos()
    {
        return $this->hasManyThrough(
            Abonos::class,
            Creditos::class,
            'id_ruta', // FK en créditos
            'id_credito', // FK en abonos
            'id_ruta', // PK en ruta
            'id_credito' // PK en créditos
        );
    }

    /**
     * Scope para rutas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para rutas de un usuario específico (ahora usa la relación muchos-a-muchos)
     */
    public function scopeDeUsuario($query, $userId)
    {
        return $query->whereHas('usuarios', function($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope para rutas de una oficina específica
     */
    public function scopeDeOficina($query, $oficinaId)
    {
        return $query->where('id_oficina', $oficinaId);
    }

    /**
     * Obtener el nombre completo de la ruta (con código)
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->codigo} - {$this->nombre}";
    }

    /**
     * Verificar si la ruta pertenece a un usuario (ahora verifica la relación muchos-a-muchos)
     */
    public function perteneceAUsuario($userId)
    {
        return $this->usuarios()->where('users.id', $userId)->exists();
    }

    /**
     * Obtener el usuario principal (alias para compatibilidad)
     */
    public function getUsuarioAttribute()
    {
        return $this->usuarioPrincipal;
    }
}