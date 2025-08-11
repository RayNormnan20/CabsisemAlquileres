<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ConceptoCredito;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\Concepto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // AGREGAR ESTA LÍNEA

class CreditoController extends Controller
{

    public function actualizarDatosCredito(Request $request)
    {
        try {
            $request->validate([
                'credito_id' => 'required|exists:creditos,id_credito',
                'nuevo_interes' => 'required|numeric',
                'forma_pago' => 'required|integer',
                'nueva_cuenta' => 'required|numeric',
                'valor_cuota' =>'required|numeric',
                'fecha_vencimiento' => 'required|date',
                'fecha_credito' => 'nullable|date', // Agregar validación para fecha editada
                'descuento' => 'nullable|numeric|min:0', // Nuevo campo
            ]);

            DB::beginTransaction();

            $credito = Creditos::find($request->credito_id);

            if (!$credito) {
                return response()->json(['error' => 'Crédito no encontrado'], 404);
            }

            // Aplicar descuento si se proporciona
            if ($request->descuento && $request->descuento > 0) {
                // Validar que el descuento no sea mayor al saldo actual
                if ($request->descuento > $credito->saldo_actual) {
                    return response()->json([
                        'error' => 'El descuento no puede ser mayor al saldo actual'
                    ], 400);
                }

                // Buscar el concepto "Abono de Descuento"
                $conceptoDescuento = Concepto::where('nombre', 'Abono de Descuento')->first();
                if (!$conceptoDescuento) {
                    // Si no existe, crear el concepto
                    $conceptoDescuento = Concepto::create([
                        'nombre' => 'Abono de Descuento',
                        'descripcion' => 'Descuento aplicado al crédito'
                    ]);
                }

                // Crear un abono por el descuento
                Abonos::create([
                    'id_credito' => $credito->id_credito,
                    'id_cliente' => $credito->id_cliente,
                    'id_ruta' => $credito->id_ruta,
                    'id_usuario' => auth()->id(),
                    'fecha_pago' => now(),
                    'monto_abono' => $request->descuento,
                    'saldo_anterior' => $credito->saldo_actual,
                    'saldo_posterior' => $credito->saldo_actual - $request->descuento,
                    'observaciones' => 'Descuento aplicado en bajo cuenta',
                    'id_concepto' => $conceptoDescuento->id,
                    'estado' => true,
                ]);

                // Aplicar el descuento al crédito
                $credito->aplicarDescuento($request->descuento);
            }

            // Actualizar los demás campos
            $credito->porcentaje_interes = $request->nuevo_interes;
            $credito->dias_plazo = $request->forma_pago;
            $credito->valor_cuota = $request->valor_cuota;
            $credito->saldo_actual = $request->nueva_cuenta;
            $credito->fecha_vencimiento = $request->fecha_vencimiento;
            
            // Usar la fecha editada si se proporciona, sino usar la fecha actual
            if ($request->fecha_credito) {
                $credito->fecha_credito = $request->fecha_credito;
            }

            $credito->save();

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Actualización de Crédito',
                "Crédito actualizado para cliente: {$credito->cliente->nombre_completo}" .
                ($request->descuento ? ", Descuento aplicado: S/ " . number_format($request->descuento, 2) : ""),
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $credito->id_credito,
                    'descuento_aplicado' => $request->descuento ?? 0,
                    'cliente_id' => $credito->id_cliente,
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Datos del crédito actualizados correctamente' .
                           ($request->descuento ? ' con descuento aplicado' : ''),
                'credito' => $credito
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar crédito',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function habilitarRenovacion(Request $request)
    {
        try {
            $request->validate([
                'credito_id' => 'required|exists:creditos,id_credito',
            ]);

            DB::beginTransaction();

            $credito = Creditos::findOrFail($request->credito_id);

            // Verificar que el crédito tenga saldo pendiente
            if ($credito->saldo_actual <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El crédito ya está pagado y no puede ser renovado.',
                ], 400);
            }

            // Verificar que no esté ya habilitado para renovación
            if ($credito->por_renovar) {
                return response()->json([
                    'success' => false,
                    'message' => 'El crédito ya está habilitado para renovación.',
                ], 400);
            }

            // Verificar que el crédito esté vencido
            $fechaHoy = Carbon::now();
            $fechaVencimiento = Carbon::parse($credito->fecha_vencimiento);

            if (!$fechaHoy->gt($fechaVencimiento)) {
                $diasRestantes = $fechaVencimiento->diffInDays($fechaHoy);
                return response()->json([
                    'success' => false,
                    'message' => "El crédito aún no está vencido. Faltan {$diasRestantes} días para el vencimiento.",
                ], 400);
            }

            $diasVencidos = $fechaHoy->diffInDays($fechaVencimiento);

            // Habilitar para renovación
            $credito->por_renovar = true;
            $credito->save();

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Habilitación para Renovación',
                "Crédito habilitado para renovación - Cliente: {$credito->cliente->nombre_completo}, Ruta: {$credito->ruta->nombre}, Días vencidos: {$diasVencidos}",
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $credito->id_credito,
                    'cliente_id' => $credito->id_cliente,
                    'ruta_id' => $credito->id_ruta,
                    'dias_vencidos' => $diasVencidos
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Crédito habilitado para renovación exitosamente. Días vencidos: {$diasVencidos}",
                'credito' => [
                    'id' => $credito->id_credito,
                    'cliente' => $credito->cliente->nombre_completo,
                    'por_renovar' => $credito->por_renovar,
                    'dias_vencidos' => $diasVencidos
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al habilitar renovación: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $credito = Creditos::findOrFail($request->id);

            // Aplicar descuento si se proporciona (solo actualizar el saldo, sin crear abono)
            if ($request->descuento && $request->descuento > 0) {
                // Validar que el descuento no sea mayor al saldo actual
                if ($request->descuento > $credito->saldo_actual) {
                    return response()->json([
                        'error' => 'El descuento no puede ser mayor al saldo actual'
                    ], 400);
                }

                // Aplicar el descuento directamente al saldo sin crear abono
                $credito->saldo_actual = $credito->saldo_actual - $request->descuento;
            }

            // Actualizar los demás campos
            $credito->porcentaje_interes = $request->nuevo_interes;
            $credito->dias_plazo = $request->forma_pago;
            $credito->valor_cuota = $request->valor_cuota;
            $credito->saldo_actual = $request->nueva_cuenta;
            $credito->fecha_vencimiento = $request->fecha_vencimiento;

            $credito->save();

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Actualización de Crédito',
                "Crédito actualizado para cliente: {$credito->cliente->nombre_completo}" .
                ($request->descuento ? ", Descuento aplicado: S/ " . number_format($request->descuento, 2) : ""),
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $credito->id_credito,
                    'descuento_aplicado' => $request->descuento ?? 0,
                    'cliente_id' => $credito->id_cliente,
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Datos del crédito actualizados correctamente' .
                           ($request->descuento ? ' con descuento aplicado' : ''),
                'credito' => $credito
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar crédito',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function renovar(Request $request)
    {
        try {
            DB::beginTransaction();

            // Buscar el crédito original
            $credito = Creditos::findOrFail($request->id);

            if ($request->descuento && $request->descuento > 0) {
                // Validar que el descuento no sea mayor al saldo actual
                if ($request->descuento > $credito->saldo_actual) {
                    return response()->json([
                        'error' => 'El descuento no puede ser mayor al saldo actual'
                    ], 400);
                }

                // Aplicar el descuento directamente al saldo sin crear abono
                $credito->saldo_actual = $credito->saldo_actual - $request->descuento;
            }

            // Actualizar solo los campos permitidos (excluyendo forma_pago)
            $credito->valor_credito = $request->valor_credito;
            $credito->saldo_actual = $request->valor_credito;
            $credito->numero_cuotas = $request->numero_cuotas;
            $credito->dias_plazo = $request->dias_plazo;
            $credito->porcentaje_interes = $request->porcentaje_interes;
            $credito->fecha_vencimiento = $request->fecha_vencimiento;
            
            // Usar la fecha editada si se proporciona, sino usar la fecha actual
            if ($request->fecha_credito) {
                $credito->fecha_credito = $request->fecha_credito;
            } else {
                $credito->fecha_credito = now();
            }
            
            // Cambiar por_renovar a false ya que se está procesando la renovación
            $credito->por_renovar = false;

            // Calcular días restantes
            $fechaHoy        = Carbon::now();
            $fechaVencimiento = Carbon::parse($request->fecha_vencimiento);
            $diasRestantes    = $fechaHoy->diffInDays($fechaVencimiento);

            // Validar para evitar división por cero
            if ($diasRestantes <= 0) {
                return response()->json([
                    'message' => 'La fecha de vencimiento debe ser posterior a hoy.',
                ], 400);
            }

            $credito->numero_cuotas = $diasRestantes;

            // Calcular cuota diaria
            $cuotaDiaria = round($request->valor_credito / $diasRestantes, 2);
            $credito->valor_cuota = $cuotaDiaria;

            // Guardar los cambios
            $credito->save();

            // Guardar los medios de pago nuevos
            if ($request->has('medios_pago') && is_array($request->medios_pago)) {
                foreach ($request->medios_pago as $mp) {
                    $credito->conceptosCredito()->create([
                        'tipo_concepto' => $mp['tipo'],
                        'monto'         => $mp['monto'],
                    ]);
                }
            }

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Renovación de Crédito',
                "Crédito renovado para cliente: {$credito->cliente->nombre_completo}" .
                ($request->descuento ? ", Descuento aplicado: S/ " . number_format($request->descuento, 2) : ""),
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $credito->id_credito,
                    'descuento_aplicado' => $request->descuento ?? 0,
                    'cliente_id' => $credito->id_cliente,
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Crédito renovado correctamente' .
                           ($request->descuento ? ' con descuento aplicado' : '') . '.',
                'cuota_diaria_calculada' => $cuotaDiaria,
                'dias_restantes' => $diasRestantes
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al renovar crédito.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelar(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'credito_id' => 'required|exists:creditos,id_credito',
            ]);

            // Buscar el crédito
            $credito = Creditos::findOrFail($request->credito_id);

            // Verificar que el crédito tenga saldo pendiente
            if ($credito->saldo_actual <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'El crédito ya está cancelado.',
                ], 400);
            }

            // Buscar el concepto "Abono" con validación
            $conceptoAbono = \App\Models\Concepto::where('nombre', 'Abono')->first();
            if (!$conceptoAbono) {
                throw new \Exception('El concepto "Abono" no existe en la base de datos. Ejecute los seeders.');
            }

            // Crear el abono directamente
            $abono = \App\Models\Abonos::create([
                'id_credito' => $credito->id_credito,
                'id_cliente' => $credito->id_cliente,
                'id_ruta' => $credito->id_ruta,
                'id_usuario' => auth()->id(),
                'fecha_pago' => now(),
                'monto_abono' => $credito->saldo_actual,
                'saldo_anterior' => $credito->saldo_actual,
                'saldo_posterior' => 0,
                'observaciones' => 'Cancelación de crédito',
                'id_concepto' => $conceptoAbono->id,
                'estado' => true,
            ]);

            // Crear el concepto de cancelación en conceptos_abono
            $conceptoAbonoCreado = $abono->conceptosabonos()->create([
                'id_usuario' => auth()->id(),
                'tipo_concepto' => 'Cancelado',
                'monto' => $credito->saldo_actual,
                'referencia' => 'Cancelación de crédito',
                'id_caja' => 1
            ]);
            // Actualizar el saldo del crédito
            $credito->saldo_actual = 0;
            $credito->save();
            Log::info('Saldo actualizado');

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Cancelación de Crédito',
                "Crédito cancelado para cliente: {$credito->cliente->nombre_completo}, Ruta: {$credito->ruta->nombre}, Monto: S/ " . number_format($abono->monto_abono, 2),
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $credito->id_credito,
                    'monto_cancelado' => $abono->monto_abono,
                    'cliente_id' => $credito->id_cliente,
                    'ruta_id' => $credito->id_ruta
                ]
            );
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Crédito cancelado correctamente.',
                'abono_id' => $abono->id_abono,
                'monto_cancelado' => $abono->monto_abono
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Error al cancelar crédito: ' . $e->getMessage(),
                'details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }




    // ========================================
    // NUEVAS FUNCIONES CON CACHÉ (SOLO AGREGAR AL FINAL)
    // ========================================

    /**
     * Obtener créditos activos con caché - NUEVA FUNCIÓN
     */
    public function index(Request $request)
    {
        $rutaId = $request->get('ruta_id');

        $creditos = Creditos::getCreditosActivosConCache($rutaId);

        return response()->json([
            'success' => true,
            'data' => $creditos,
            'total' => $creditos->count()
        ]);
    }

    /**
     * Obtener créditos vencidos con caché - NUEVA FUNCIÓN
     */
    public function vencidos(Request $request)
    {
        $rutaId = $request->get('ruta_id');

        $creditosVencidos = Creditos::getCreditosVencidosConCache($rutaId);

        return response()->json([
            'success' => true,
            'data' => $creditosVencidos,
            'total' => $creditosVencidos->count()
        ]);
    }

    /**
     * Obtener estadísticas con caché - NUEVA FUNCIÓN
     */
    public function estadisticas(Request $request)
    {
        $rutaId = $request->get('ruta_id');

        $estadisticas = Creditos::getEstadisticasCreditosConCache($rutaId);

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ]);
    }

    /**
     * Obtener conceptos con caché - NUEVA FUNCIÓN
     */
    public function getConceptosConCache()
    {
        $conceptos = Cache::remember('conceptos_creditos', 3600, function () {
            return Concepto::select('id', 'nombre', 'descripcion')
                          ->orderBy('nombre')
                          ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $conceptos
        ]);
    }

    /**
     * Limpiar caché específico de créditos - NUEVA FUNCIÓN
     */
    public function limpiarCache(Request $request)
    {
        $rutaId = $request->get('ruta_id');

        if ($rutaId) {
            // Limpiar caché de una ruta específica
            Cache::forget("creditos_activos_ruta_{$rutaId}");
            Cache::forget("creditos_vencidos_ruta_{$rutaId}");
            Cache::forget("estadisticas_creditos_ruta_{$rutaId}");
        } else {
            // Limpiar todo el caché de créditos
            Cache::forget('creditos_activos_all');
            Cache::forget('creditos_vencidos_all');
            Cache::forget('estadisticas_creditos_all');
            Cache::forget('conceptos_creditos');
        }

        return response()->json([
            'success' => true,
            'message' => 'Caché limpiado correctamente'
        ]);
    }
}