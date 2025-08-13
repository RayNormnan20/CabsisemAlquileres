<?php

namespace App\Models;

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

    // Accessor para obtener el nombre completo del cliente
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
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
}