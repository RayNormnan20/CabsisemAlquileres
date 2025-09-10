<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\ClienteCreated;
use App\Events\ClienteUpdated;

class Clientes extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

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
        'foto1_path',
        'foto2_path',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($cliente) {
            event(new ClienteCreated($cliente));
        });

        static::updated(function ($cliente) {
            event(new ClienteUpdated($cliente));
        });
    }

    // Relación con TipoDocumento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento');
    }

    public static function listarPorRuta($idRuta)
    {
        return self::where('id_ruta', $idRuta)
            ->get()
            ->mapWithKeys(fn($c) => [$c->id_cliente => $c->nombre_completo]);
    }


    public function yapeCliente()
    {
        return $this->hasMany(YapeCliente::class, 'id_cliente', 'id_cliente');
    }

    // Relación con Ruta (¡NUEVA RELACIÓN!)
    // Un cliente pertenece a una ruta
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    // Relación con Créditos
    public function creditos()
    {
        return $this->hasMany(Creditos::class, 'id_cliente');
    }

    // Relación directa con abonos (si la tabla abonos tiene id_cliente)
    // Esto es útil si un abono puede existir sin un crédito directo o si necesitas un acceso rápido
    public function abonosDirectos()
    {
        return $this->hasMany(Abonos::class, 'id_cliente');
    }

    // Relación a través de créditos para obtener todos los abonos de un cliente
    public function abonos()
    {
        return $this->hasManyThrough(
            Abonos::class,
            Creditos::class,
            'id_cliente', // FK en créditos
            'id_credito', // FK en abonos
            'id_cliente', // PK en clientes
            'id_credito'  // PK en créditos
        );
    }

    // Accessor para obtener el nombre completo del cliente
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }


    public function scopeDeRuta($query, $rutaId)
    {
        return $query->where('id_ruta', $rutaId);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    public function getFoto1UrlAttribute()
    {
        return $this->foto1_path ? asset('storage/' . $this->foto1_path) : null;
    }

    public function getFoto2UrlAttribute()
    {
        return $this->foto2_path ? asset('storage/' . $this->foto2_path) : null;
    }

    // Método para obtener el crédito activo del cliente
    public function creditoActivo()
    {
        return $this->creditos()->where('saldo_actual', '>', 0)->first();
    }
}