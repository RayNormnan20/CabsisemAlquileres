<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Abonos extends Model
{
    use HasFactory;

    protected $table = 'abonos';
    protected $primaryKey = 'id_abono';

    protected $fillable = [
        'id_credito',
        'id_cliente',
        'id_ruta',
        'id_usuario',
        'id_concepto', // Añadido para relación con conceptos
        'id_yape_cliente', // Solo el ID
        'fecha_pago',
        'monto_abono',
        'saldo_anterior',
        'saldo_posterior',
        'coordenadas_gps',
        'observaciones',
        'estado',
        'activar_segundo_recorrido'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto_abono' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_posterior' => 'decimal:2',
        'coordenadas_gps' => 'array',
        'estado' => 'boolean',
        'activar_segundo_recorrido' => 'boolean'
    ];

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Cuando se crea un abono
        static::created(function ($abono) {
            if ($abono->id_credito) {
                $abono->actualizarSegundoRecorridoCredito();
                
                // Resetear el checkbox a false después de crear el abono
                if ($abono->activar_segundo_recorrido) {
                    $abono->activar_segundo_recorrido = false;
                    $abono->saveQuietly(); // Usar saveQuietly para evitar disparar eventos infinitos
                }
            }
        });

        // Cuando se actualiza un abono
        static::updated(function ($abono) {
            if ($abono->id_credito && $abono->wasChanged('activar_segundo_recorrido')) {
                $abono->actualizarSegundoRecorridoCredito();
            }
        });

        // EVENTOS DESHABILITADOS: No actualizar automáticamente el campo 'entregar'
        // El campo 'entregar' ahora representa el monto específico del Yape a entregar
        
        /*
        // Cuando se crea un abono
        static::created(function ($abono) {
            if ($abono->id_yape_cliente) {
                $abono->actualizarEntregarYapeCliente();
            }
        });

        // Cuando se actualiza un abono
        static::updated(function ($abono) {
            if ($abono->id_yape_cliente) {
                $abono->actualizarEntregarYapeCliente();
            }
        });

        // Cuando se elimina un abono
        static::deleted(function ($abono) {
            if ($abono->id_yape_cliente) {
                $abono->actualizarEntregarYapeCliente();
            }
        });
        */
    }

    /**
     * Actualiza el estado de segundo recorrido del crédito asociado
     */
    public function actualizarSegundoRecorridoCredito()
    {
        if ($this->id_credito) {
            // Actualizar el crédito según el estado del checkbox
            Creditos::where('id_credito', $this->id_credito)
                ->update(['segundo_recorrido' => $this->activar_segundo_recorrido]);
        }
    }

    // MÉTODO DESHABILITADO: No actualizar automáticamente el campo 'entregar'
    // El campo 'entregar' ahora representa el monto específico del Yape a entregar
    // y no debe ser sobrescrito con la suma de abonos
    public function actualizarEntregarYapeCliente()
    {
        // Método deshabilitado para preservar el valor original del campo 'entregar'
        // que representa el monto específico del Yape que se debe entregar
        return;
        
        /*
        if ($this->id_yape_cliente) {
            // Solo contar abonos que tienen conceptos de tipo "Yape"
            $totalEntregado = self::where('id_yape_cliente', $this->id_yape_cliente)
                ->whereHas('conceptosabonos', function($query) {
                    $query->where('tipo_concepto', 'Yape');
                })
                ->sum('monto_abono');

            YapeCliente::where('id', $this->id_yape_cliente)
                ->update(['entregar' => $totalEntregado]);
        }
        */
    }

    public function credito()
    {
        return $this->belongsTo(Creditos::class, 'id_credito');
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id_cliente');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class, 'id_concepto');
    }

    // Relación con conceptos de crédito (métodos de pago)
    public function conceptos()
    {
        return $this->hasMany(ConceptoCredito::class, 'id_abono');
    }

    // Relación con conceptos de abono (alternativa)
    public function conceptosabonos()
    {
        return $this->hasMany(ConceptoAbono::class, 'id_abono');
    }

    public function getTotalAbonadoAttribute()
    {
        return $this->conceptos->sum('monto');
    }

    public function getEstaCompletoAttribute()
    {
        // Ahora podemos usar directamente el campo estado booleano
        // o mantener esta lógica si es diferente
        return $this->estado && (abs($this->total_abonado - $this->monto_abono)) < 0.01;
    }

    public function getMetodosPagoAttribute()
    {
        return $this->conceptos->pluck('tipo_concepto')->unique()->implode(', ');
    }

    public static function registrarConConceptos(array $datosAbono, array $conceptos)
    {
        return DB::transaction(function () use ($datosAbono, $conceptos) {
            if (empty($datosAbono['id_credito'])) {
                throw new \Exception('Debe especificar un crédito para el abono');
            }

            // Asegurar que el concepto "Abono" esté asignado
            if (empty($datosAbono['id_concepto'])) {
                $conceptoAbono = Concepto::where('nombre', 'Abono')->first();
                if (!$conceptoAbono) {
                    throw new \Exception('El concepto "Abono" no existe en la base de datos');
                }
                $datosAbono['id_concepto'] = $conceptoAbono->id;
            }

            // Estado por defecto true (completo)
            $datosAbono['estado'] = true;

            $abono = self::create($datosAbono);

            foreach ($conceptos as $concepto) {
                $abono->conceptos()->create($concepto);
            }

            $abono->actualizarSaldos();

            return $abono;
        });
    }

    public function actualizarSaldos()
    {
        $this->saldo_posterior = $this->saldo_anterior - $this->total_abonado;

        // Marcar como completo si el saldo posterior es coherente
        $this->estado = abs($this->saldo_posterior - ($this->saldo_anterior - $this->total_abonado)) < 0.01;

        $this->save();

        $this->credito->actualizarSaldo();
    }

    public static function obtenerHistorialCompleto($creditoId)
    {
        return self::with(['conceptos', 'usuario', 'concepto'])
            ->where('id_credito', $creditoId)
            ->orderBy('fecha_pago', 'desc')
            ->get()
            ->map(function ($abono) {
                return [
                    'fecha' => $abono->fecha_pago->format('d/m/Y'),
                    'hora' => $abono->fecha_pago->format('H:i'),
                    'concepto_principal' => $abono->concepto->nombre ?? 'Abono',
                    'conceptos' => $abono->conceptos->map(function ($concepto) {
                        return [
                            'tipo' => $concepto->tipo_concepto,
                            'monto' => 'S/ ' . number_format($concepto->monto, 2),
                            'comprobante' => $concepto->foto_comprobante
                        ];
                    }),
                    'total' => 'S/ ' . number_format($abono->total_abonado, 2),
                    'saldo' => 'S/ ' . number_format($abono->saldo_posterior, 2),
                    'usuario' => $abono->usuario->name,
                    'completo' => $abono->estado ? 'Sí' : 'No',
                    'gps' => $abono->coordenadas_gps ? '✔' : ''
                ];
            });
    }

    public static function validarMonto($creditoId, $monto)
    {
        $credito = Creditos::findOrFail($creditoId);
        return $monto <= $credito->saldo_actual;
    }

    // Relación con YapeCliente
    public function yapeCliente()
    {
        return $this->belongsTo(\App\Models\YapeCliente::class, 'id_yape_cliente');
    }
}
