<?php

namespace App\Models;

use App\Events\ClienteAlquilerCreated;
use App\Events\ClienteAlquilerUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteAlquiler extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes_alquiler';
    protected $primaryKey = 'id_cliente_alquiler';

    protected $fillable = [
        'id_tipo_documento',
        'numero_documento',
        'nombre',
        'apellido',
        'celular',
        'telefono',
        'direccion',
        'direccion2',
        'ciudad',
        'nombre_negocio',
        'activo',
        'id_ruta',
        'id_usuario_creador',
      //  'foto1_path',
       // 'foto2_path',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con TipoDocumento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento');
    }

    // Relación con Ruta
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta', 'id_ruta');
    }

    // Relación con el usuario creador
    public function creador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    // Relación con alquileres
    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'id_cliente_alquiler', 'id_cliente_alquiler');
    }

    // Accessor para obtener el nombre completo del cliente
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    // Método para verificar si el cliente tiene alquileres activos
    public function tieneAlquilerActivo()
    {
        return $this->alquileres()
            ->where('estado_alquiler', 'activo')
            ->exists();
    }

    // Scope para obtener solo clientes disponibles (sin alquileres activos)
    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)
            ->whereDoesntHave('alquileres', function ($q) {
                $q->where('estado_alquiler', 'activo');
            });
    }

    // Scope para obtener clientes con alquileres activos
    public function scopeConAlquilerActivo($query)
    {
        return $query->where('activo', true)
            ->whereHas('alquileres', function ($q) {
                $q->where('estado_alquiler', 'activo');
            });
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($clienteAlquiler) {
            ClienteAlquilerCreated::dispatch($clienteAlquiler);
        });

        static::updated(function ($clienteAlquiler) {
            ClienteAlquilerUpdated::dispatch($clienteAlquiler);
        });
    }

    // Scope para filtrar por ruta
    public function scopeDeRuta($query, $rutaId)
    {
        return $query->where('id_ruta', $rutaId);
    }

    /*
    // Método para obtener URL de fotos
    public function getFoto1UrlAttribute()
    {
        return $this->foto1_path ? asset('storage/' . $this->foto1_path) : null;
    }

    public function getFoto2UrlAttribute()
    {
        return $this->foto2_path ? asset('storage/' . $this->foto2_path) : null;
    }
        */

    // Método estático para listar por ruta
    public static function listarPorRuta($idRuta)
    {
        return self::where('id_ruta', $idRuta)
            ->get()
            ->mapWithKeys(fn($c) => [$c->id_cliente_alquiler => $c->nombre_completo]);
    }

    // Relación con Edificios (como propietario)
    public function edificiosPropios()
    {
        return $this->hasMany(Edificio::class, 'id_cliente_alquiler', 'id_cliente_alquiler');
    }

    // Relación con Alquileres (como inquilino)
    public function alquileresComoInquilino()
    {
        return $this->hasMany(Alquiler::class, 'id_cliente_alquiler', 'id_cliente_alquiler');
    }
}
