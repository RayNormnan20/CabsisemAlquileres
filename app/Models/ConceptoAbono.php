<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\ConceptoAbonoCreated;
use App\Events\ConceptoAbonoUpdated;
use App\Events\ConceptoAbonoDeleted;

class ConceptoAbono extends Model
{
    protected $table = 'conceptos_abono';
    protected $primaryKey = 'id_concepto_abono';

    protected $fillable = [
        'id_abono',
        'id_usuario',
        'id_ruta',
        'tipo_concepto',
        'monto',
        'foto_comprobante',
        'referencia',
        'fecha_concepto',
        'id_caja'
    ];

    protected $casts = [
        'fecha_concepto' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function abono()
    {
        return $this->belongsTo(Abonos::class, 'id_abono');
    }


     public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conceptoAbono) {
            // Asignar id_usuario automáticamente para todos los tipos de concepto
            if (!$conceptoAbono->id_usuario) {
                $conceptoAbono->id_usuario = auth()->id();
            }
            
            // Asignar fecha_concepto automáticamente si no se especifica
            if (!$conceptoAbono->fecha_concepto) {
                $conceptoAbono->fecha_concepto = now();
            }
            
            // Si no tiene ruta asignada, obtener la ruta de la sesión
            if (!$conceptoAbono->id_ruta) {
                // Prioridad 1: Ruta seleccionada en la sesión
                $rutaSesion = session('selected_ruta_id');
                if ($rutaSesion) {
                    $conceptoAbono->id_ruta = $rutaSesion;
                }
                // Prioridad 2: Si tiene abono asociado, obtener la ruta del abono
                elseif ($conceptoAbono->id_abono) {
                    $abono = Abonos::find($conceptoAbono->id_abono);
                    if ($abono && $abono->id_ruta) {
                        $conceptoAbono->id_ruta = $abono->id_ruta;
                    }
                }
                /*
                // Prioridad 3: Si tiene usuario, obtener la primera ruta del usuario
                elseif ($conceptoAbono->id_usuario) {
                    $usuario = User::find($conceptoAbono->id_usuario);
                    if ($usuario) {
                        $ruta = $usuario->rutas()->first();
                        if ($ruta) {
                            $conceptoAbono->id_ruta = $ruta->id_ruta;
                        }
                    }
                }
                    */
            }
        });
        
        static::created(function ($conceptoAbono) {
            // Disparar evento WebSocket cuando se crea un concepto de abono
            ConceptoAbonoCreated::dispatch($conceptoAbono);
        });
        
        static::updating(function ($conceptoAbono) {
            // Asignar id_usuario automáticamente cuando se edita
            if (!$conceptoAbono->id_usuario) {
                $conceptoAbono->id_usuario = auth()->id();
            }
        });
        
        static::updated(function ($conceptoAbono) {
            // Disparar evento WebSocket cuando se actualiza un concepto de abono
            ConceptoAbonoUpdated::dispatch($conceptoAbono);
        });
        
        static::deleted(function ($conceptoAbono) {
            // Disparar evento WebSocket cuando se elimina un concepto de abono
            ConceptoAbonoDeleted::dispatch($conceptoAbono);
        });
    }

/*
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
        */
}
