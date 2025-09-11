<?php

namespace App\Models;

use App\Events\AlquilerCreated;
use App\Events\AlquilerUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Alquiler extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alquileres';
    protected $primaryKey = 'id_alquiler';

    protected $fillable = [
        'id_departamento',
        'id_cliente_alquiler', // Inquilino
        'fecha_inicio',
        'fecha_fin',
        'precio_mensual',
        'deposito_garantia',
        'fecha_proximo_pago',
        'dia_pago', // Día del mes para pago
        'estado_alquiler', // activo, finalizado, suspendido
        'observaciones',
        'contrato_path', // Ruta del archivo del contrato
        'id_usuario_creador'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_proximo_pago' => 'date',
        'precio_mensual' => 'decimal:2',
        'deposito_garantia' => 'decimal:2',
        'dia_pago' => 'integer'
    ];

    // Relación con Departamento
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    // Relación con ClienteAlquiler (inquilino)
    public function inquilino()
    {
        return $this->belongsTo(ClienteAlquiler::class, 'id_cliente_alquiler', 'id_cliente_alquiler');
    }

    // Relación con usuario creador
    public function creador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    // Relación con pagos de alquiler
    public function pagos()
    {
        return $this->hasMany(PagoAlquiler::class, 'id_alquiler', 'id_alquiler');
    }

    // Accessor para URL del contrato
    public function getContratoUrlAttribute()
    {
        return $this->contrato_path ? asset('storage/' . $this->contrato_path) : null;
    }

    // Scope para alquileres activos
    public function scopeActivos($query)
    {
        return $query->where('estado_alquiler', 'activo');
    }

    // Scope para alquileres vencidos
    public function scopeVencidos($query)
    {
        return $query->where('fecha_proximo_pago', '<', Carbon::now())
                    ->where('estado_alquiler', 'activo');
    }

    // Método para calcular días de atraso
    public function getDiasAtrasoAttribute()
    {
        if ($this->fecha_proximo_pago && $this->fecha_proximo_pago < Carbon::now()) {
            return Carbon::now()->diffInDays($this->fecha_proximo_pago);
        }
        return 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($alquiler) {
            AlquilerCreated::dispatch($alquiler);
        });

        static::updated(function ($alquiler) {
            AlquilerUpdated::dispatch($alquiler);
        });
    }
}