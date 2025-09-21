<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache; // AGREGAR ESTA LÍNEA
use App\Events\CreditoCreated;
use App\Events\CreditoUpdated;
use App\Events\MovimientoCreated;
use App\Events\MovimientoUpdated;
use App\Events\PlanillaRecaudadorCreated;
use App\Events\PlanillaRecaudadorUpdated;

class Creditos extends Model
{
    use HasFactory;

    protected $table = 'creditos';
    protected $primaryKey = 'id_credito';

    protected $fillable = [
        'id_cliente',
        'id_ruta',
        'id_usuario_creador',
        'fecha_credito',
        'id_concepto',
        'valor_credito',
        'porcentaje_interes',
        'forma_pago',
        'dias_plazo',
        'orden_cobro',
        'saldo_actual',
        'descuento_aplicado', // Nuevo campo
        'valor_cuota',
        'numero_cuotas',
        'fecha_vencimiento',
        'fecha_proximo_pago',
        'llamada_cliente',
        'revisado',
        'analizado',
        'por_renovar',
        'segundo_recorrido',
        'segundo_cobrador',
        'es_adicional'
    ];

    protected $casts = [
        'fecha_credito' => 'date',
        'valor_credito' => 'decimal:2',
        'porcentaje_interes' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2', // Nuevo cast
        'valor_cuota' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_proximo_pago' => 'date',
        'llamada_cliente' => 'boolean',
        'revisado' => 'boolean',
        'analizado' => 'boolean',
        'por_renovar' => 'boolean',
        'segundo_recorrido' => 'boolean',
        'es_adicional' => 'boolean'
    ];

    protected $attributes = [
        'llamada_cliente' => false,
        'revisado' => false,
        'analizado' => false,
        'por_renovar' => false,
        'segundo_recorrido' => false,
        'es_adicional' => false
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($credito) {
            // Asignar automáticamente el usuario autenticado al crear un crédito
            if (!$credito->id_usuario_creador && auth()->check()) {
                $credito->id_usuario_creador = auth()->id();
            }
        });

        static::created(function ($credito) {
            event(new CreditoCreated($credito));

            // Disparar evento de movimiento para WebSocket de Ingresos y Gastos
             $movimiento = new \App\Models\Movimiento();
             $movimiento->fill([
                 'id' => $credito->id_credito,
                 'tipo_movimiento' => 'Crédito',
                 'fecha' => $credito->fecha_credito,
                 'monto' => $credito->valor_credito,
                 'concepto' => $credito->concepto->nombre ?? 'Crédito',
                 'tipo_concepto' => $credito->concepto->tipo ?? 'Gastos'
             ]);
             event(new MovimientoCreated($movimiento));

             // Disparar evento de planilla recaudador
             $planillaData = [
                 'id_credito' => $credito->id_credito,
                 'cliente_completo' => $credito->cliente->nombre ?? 'Cliente',
                 'valor_credito' => $credito->valor_credito,
                 'fecha_credito' => $credito->fecha_credito
             ];
             event(new PlanillaRecaudadorCreated($planillaData));
        });

        static::updated(function ($credito) {
            event(new CreditoUpdated($credito));

            // Disparar evento de movimiento para WebSocket de Ingresos y Gastos
             $movimiento = new \App\Models\Movimiento();
             $movimiento->fill([
                 'id' => $credito->id_credito,
                 'tipo_movimiento' => 'Crédito',
                 'fecha' => $credito->fecha_credito,
                 'monto' => $credito->valor_credito,
                 'concepto' => $credito->concepto->nombre ?? 'Crédito',
                 'tipo_concepto' => $credito->concepto->tipo ?? 'Gastos'
             ]);
             event(new MovimientoUpdated($movimiento));

             // Disparar evento de planilla recaudador
             $planillaData = [
                 'id_credito' => $credito->id_credito,
                 'cliente_completo' => $credito->cliente->nombre ?? 'Cliente',
                 'valor_credito' => $credito->valor_credito,
                 'fecha_credito' => $credito->fecha_credito
             ];
             event(new PlanillaRecaudadorUpdated($planillaData));
        });
    }

// En el modelo Credito
    public function concepto()
    {
        return $this->belongsTo(Concepto::class, 'id_concepto');
    }
    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id_cliente');
    }

    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class, 'forma_pago');
    }

    public function ordenCobro()
    {
        return $this->belongsTo(OrdenCobro::class, 'orden_cobro');
    }

    public function abonos()
    {
        return $this->hasMany(Abonos::class, 'id_credito');
    }

    public function conceptosCredito()
    {
        return $this->hasMany(ConceptoCredito::class, 'id_credito');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    public function yapeCliente()
    {
        return $this->hasOne(YapeCliente::class, 'id_credito');
    }

    public function scopeActivos($query)
    {
        return $query->where('saldo_actual', '>', 0);
    }

    public function scopePagados($query)
    {
        return $query->where('saldo_actual', '<=', 0);
    }

    public function getInteresTotalAttribute()
    {
        return $this->valor_credito * ($this->porcentaje_interes / 100);
    }

    public function getMontoTotalAttribute()
    {
        return $this->valor_credito + $this->interes_total;
    }

    public function getCuotasPagadasAttribute()
    {
        return $this->abonos()->count();
    }

    public function getCuotasPendientesAttribute()
    {
        return max(0, $this->numero_cuotas - $this->cuotas_pagadas);
    }

    public function scopeDeRuta($query, $rutaId)
    {
        return $query->where('id_ruta', $rutaId);
    }

    public function actualizarSaldo()
    {
        if ($this->es_adicional) {
            // Para créditos adicionales, solo restar los abonos del saldo actual
            // No recalcular desde valor_credito porque el saldo aumenta diariamente
            $totalAbonos = $this->abonos()->sum('monto_abono');
            // El saldo actual ya incluye las cuotas diarias aplicadas
            // Solo necesitamos asegurar que los abonos se resten correctamente
            // No modificamos el saldo aquí para créditos adicionales
        } else {
            // Para créditos normales, calcular como siempre
            $this->saldo_actual = $this->valor_credito - $this->abonos()->sum('monto_abono');
            $this->save();
        }
    }

    /**
     * Aplicar descuento al crédito
     */
    public function aplicarDescuento($montoDescuento)
    {
        $this->descuento_aplicado += $montoDescuento;
        $this->saldo_actual -= $montoDescuento;
        $this->save();

        return $this;
    }

    // ========================================
    // NUEVAS FUNCIONES DE CACHÉ (SOLO AGREGAR)
    // ========================================

    /**
     * Obtener créditos activos con caché - NUEVA FUNCIÓN
     */
    public static function getCreditosActivosConCache($rutaId = null)
    {
        $cacheKey = $rutaId ? "creditos_activos_ruta_{$rutaId}" : 'creditos_activos_all';

        return Cache::remember($cacheKey, 1800, function () use ($rutaId) { // 30 minutos
            $query = self::with(['cliente', 'ruta', 'tipoPago'])
                        ->where('saldo_actual', '>', 0);

            if ($rutaId) {
                $query->where('id_ruta', $rutaId);
            }

            return $query->orderBy('fecha_credito', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
        });
    }

    /**
     * Obtener créditos vencidos con caché - NUEVA FUNCIÓN
     */
    public static function getCreditosVencidosConCache($rutaId = null)
    {
        $cacheKey = $rutaId ? "creditos_vencidos_ruta_{$rutaId}" : 'creditos_vencidos_all';

        return Cache::remember($cacheKey, 900, function () use ($rutaId) { // 15 minutos
            $query = self::with(['cliente', 'ruta', 'tipoPago'])
                        ->where('saldo_actual', '>', 0)
                        ->where('fecha_vencimiento', '<', now());

            if ($rutaId) {
                $query->where('id_ruta', $rutaId);
            }

            return $query->orderBy('fecha_credito', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
        });
    }

    /**
     * Obtener estadísticas de créditos con caché - NUEVA FUNCIÓN
     */
    public static function getEstadisticasCreditosConCache($rutaId = null)
    {
        $cacheKey = $rutaId ? "estadisticas_creditos_ruta_{$rutaId}" : 'estadisticas_creditos_all';

        return Cache::remember($cacheKey, 3600, function () use ($rutaId) { // 1 hora
            $query = self::query();

            if ($rutaId) {
                $query->where('id_ruta', $rutaId);
            }

            return [
                'total_creditos' => $query->count(),
                'creditos_activos' => $query->where('saldo_actual', '>', 0)->count(),
                'creditos_pagados' => $query->where('saldo_actual', '<=', 0)->count(),
                'creditos_vencidos' => $query->where('saldo_actual', '>', 0)
                                            ->where('fecha_vencimiento', '<', now())->count(),
                'monto_total_activo' => $query->where('saldo_actual', '>', 0)->sum('saldo_actual'),
                'monto_total_cartera' => $query->sum('valor_credito'),
            ];
        });
    }

    /**
     * Limpiar caché cuando se modifica un crédito - NUEVA FUNCIÓN
     */
    public function limpiarCacheCredito()
    {
        $keys = [
            'creditos_activos_all',
            'creditos_vencidos_all',
            'estadisticas_creditos_all',
            "creditos_activos_ruta_{$this->id_ruta}",
            "creditos_vencidos_ruta_{$this->id_ruta}",
            "estadisticas_creditos_ruta_{$this->id_ruta}"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Override del método save para limpiar caché automáticamente - NUEVA FUNCIÓN
     */
    public function save(array $options = [])
    {
        $result = parent::save($options);

        // Limpiar caché después de guardar
        $this->limpiarCacheCredito();

        return $result;
    }
}