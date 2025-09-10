<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ConceptoCredito;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\Concepto;
use App\Models\YapeCliente;
use App\Events\CreditoCreated;
use App\Events\CreditoUpdated;
use App\Events\AbonoCreated;

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
                'tipo_operacion' => 'nullable|string', // Nuevo campo para distinguir el tipo
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
            
            DB::commit();
            
            return response()->json([
                'message' => 'Datos del crédito actualizados correctamente',
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

            $credito = Creditos::findOrFail($request->credito_id ?? $request->id);
            $saldoAnterior = $credito->saldo_actual;

            // Si es operación de bajo cuenta, crear abono del saldo actual y nuevo crédito
            if ($request->tipo_operacion === 'bajo_cuenta' && $saldoAnterior > 0) {
                // Buscar o crear el concepto "Abono de Bajo Cuenta"
                $conceptoBajoCuenta = Concepto::firstOrCreate(
                    ['nombre' => 'Abono de Bajo Cuenta'],
                    ['tipo' => 'Ingresos']
                );

                // Crear el abono del saldo actual
                $abono = Abonos::create([
                    'id_credito' => $credito->id_credito,
                    'id_cliente' => $credito->id_cliente,
                    'id_ruta' => $credito->id_ruta,
                    'id_usuario' => auth()->id(),
                    'fecha_pago' => now(),
                    'monto_abono' => $saldoAnterior,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_posterior' => 0,
                    'observaciones' => 'Abono automático por bajo cuenta',
                    'id_concepto' => $conceptoBajoCuenta->id,
                    'estado' => true,
                    'activar_segundo_recorrido' => false,
                ]);

                // Disparar evento de abono creado
                event(new AbonoCreated($abono));

                // Crear el concepto de abono en conceptos_abono
                $abono->conceptosabonos()->create([
                    'id_usuario' => auth()->id(),
                    'tipo_concepto' => 'Abono de Bajo Cuenta',
                    'monto' => $saldoAnterior,
                    'referencia' => 'Abono automático por bajo cuenta',
                    'id_caja' => 1
                ]);

                // Marcar el crédito original como pagado
                $credito->saldo_actual = 0;
                $credito->save();

                // Calcular interés para el nuevo crédito
                $diasPago = $request->forma_pago;
                $porcentajeInteres = $request->nuevo_interes / 100;
                $interesCalculado = $request->nueva_cuenta * $porcentajeInteres;
                $cuotaDiaria = ($request->nueva_cuenta + $interesCalculado) / $diasPago;
                $fechaCredito = now();

                // Crear nuevo crédito con los nuevos valores
                $nuevoCredito = Creditos::create([
                    'id_cliente' => $credito->id_cliente,
                    'id_ruta' => $credito->id_ruta,
                    'id_usuario' => auth()->id(),
                    'id_concepto' => $credito->id_concepto,
                    'forma_pago' => $credito->forma_pago, // Usar la misma forma de pago del crédito original
                    'orden_cobro' => $credito->orden_cobro,
                    'fecha_credito' => $fechaCredito,
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'fecha_proximo_pago' => $fechaCredito->copy()->addDays($diasPago),
                    'valor_credito' => $request->nueva_cuenta,
                    'porcentaje_interes' => $request->nuevo_interes,
                    'valor_cuota' => $cuotaDiaria, // Cuota diaria calculada con interés incluido
                    'numero_cuotas' => $diasPago, // Número de cuotas basado en días
                    'dias_plazo' => $diasPago, // Días de plazo
                    'saldo_actual' => $request->nueva_cuenta + $interesCalculado, // Saldo actual con interés incluido
                    'observaciones' => 'Nuevo crédito por bajo cuenta del crédito #' . $credito->id_credito,
                    'por_renovar' => false,
                    'descuento_aplicado' => 0,
                    'es_adicional' => false
                ]);

                // Aplicar descuento al nuevo crédito si se proporciona
                if ($request->descuento && $request->descuento > 0) {
                    // Validar que el descuento no sea mayor al nuevo saldo
                    if ($request->descuento > $nuevoCredito->saldo_actual) {
                        return response()->json([
                            'error' => 'El descuento no puede ser mayor al nuevo saldo'
                        ], 400);
                    }
                    $nuevoCredito->saldo_actual = $nuevoCredito->saldo_actual - $request->descuento;
                    $nuevoCredito->descuento_aplicado = $request->descuento;
                    $nuevoCredito->save();
                }

                // Procesar métodos de pago si se proporcionan
                if ($request->has('medios_pago') && is_array($request->medios_pago)) {
                    foreach ($request->medios_pago as $medioPago) {
                        if ($medioPago['monto'] > 0) {
                            // Crear registro en conceptos_credito
                            $nuevoCredito->conceptosCredito()->create([
                                'tipo_concepto' => $medioPago['tipo'],
                                'monto' => $medioPago['monto'],
                            ]);
                            
                            // Si es un pago por Yape y se proporciona nombre del cliente, crear registro en YapeCliente solo si no existe
                            if ($medioPago['tipo'] === 'Yape' && isset($medioPago['nombre_cliente']) && !empty($medioPago['nombre_cliente'])) {
                                // Extraer el nombre limpio (sin "(Nuevo)" si existe)
                                $nombreLimpio = str_replace(' (Nuevo)', '', $medioPago['nombre_cliente']);
                                
                                // Obtener el nombre completo del cliente
                                $cliente = \App\Models\Clientes::find($nuevoCredito->id_cliente);
                                $nombreCompletoCliente = $cliente ? $cliente->nombre_completo : '';
                                
                                // Verificar si el nombre original contenía "(Nuevo)" para forzar creación de nuevo registro
                                $esNuevoRegistro = strpos($medioPago['nombre_cliente'], '(Nuevo)') !== false;
                                
                                if ($esNuevoRegistro) {
                                    // Crear nuevo registro independiente para este crédito
                                    \App\Models\YapeCliente::create([
                                        'id_cliente' => $nuevoCredito->id_cliente,
                                        'id_credito' => $nuevoCredito->id_credito,
                                        'nombre' => $nombreLimpio,
                                        'user_id' => auth()->id(),
                                        'monto' => $medioPago['monto'],
                                        'entregar' => $medioPago['monto'],
                                        'valor' => $request->nueva_cuenta
                                    ]);
                                } else {
                                    // Buscar YapeCliente existente sin id_credito asignado (disponible para actualizar)
                                    $yapeClienteDisponible = \App\Models\YapeCliente::where('id_cliente', $nuevoCredito->id_cliente)
                                        ->where('nombre', $nombreLimpio)
                                        ->whereNull('id_credito')
                                        ->first();
                                    
                                    if ($yapeClienteDisponible) {
                                        // Actualizar el registro existente asignándole el nuevo crédito
                                        $yapeClienteDisponible->update([
                                            'id_credito' => $nuevoCredito->id_credito,
                                            'monto' => $yapeClienteDisponible->monto + $medioPago['monto'],
                                            'entregar' => $yapeClienteDisponible->entregar + $medioPago['monto'],
                                            'valor' => $request->nueva_cuenta
                                        ]);
                                    } else {
                                        // Buscar si ya existe un YapeCliente para este crédito específico
                                        $yapeClienteCredito = \App\Models\YapeCliente::where('id_cliente', $nuevoCredito->id_cliente)
                                            ->where('nombre', $nombreLimpio)
                                            ->where('id_credito', $nuevoCredito->id_credito)
                                            ->first();
                                        
                                        if ($yapeClienteCredito) {
                                            // Actualizar el registro del mismo crédito
                                            $yapeClienteCredito->monto += $medioPago['monto'];
                                            $yapeClienteCredito->entregar += $medioPago['monto'];
                                            $yapeClienteCredito->save();
                                        } else {
                                            // Crear nuevo registro para este crédito
                                            \App\Models\YapeCliente::create([
                                                'id_cliente' => $nuevoCredito->id_cliente,
                                                'id_credito' => $nuevoCredito->id_credito,
                                                'nombre' => $nombreLimpio,
                                                'user_id' => auth()->id(),
                                                'monto' => $medioPago['monto'],
                                                'entregar' => $medioPago['monto'],
                                                'valor' => $request->nueva_cuenta
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Disparar evento de crédito creado
                event(new CreditoCreated($nuevoCredito));

                $creditoResultado = $nuevoCredito;
            } else {
                // Si no es bajo cuenta, actualizar el crédito existente
                
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
                // Solo actualizar saldo_actual si no se aplicó descuento (para evitar sobrescribir)
                if (!($request->descuento && $request->descuento > 0)) {
                    $credito->saldo_actual = $request->nueva_cuenta;
                }
                $credito->fecha_vencimiento = $request->fecha_vencimiento;
                $credito->save();
                
                $creditoResultado = $credito;
            }

            // Registrar en el log de actividad según el tipo de operación
            $tipoActividad = ($request->tipo_operacion === 'bajo_cuenta') ? 'Bajo Cuenta' : 'Actualización de Crédito';
            $mensajeActividad = ($request->tipo_operacion === 'bajo_cuenta') 
                ? "Bajo cuenta realizado para cliente: {$creditoResultado->cliente->nombre_completo}. Nuevo crédito creado: #{$creditoResultado->id_credito}"
                : "Crédito actualizado para cliente: {$creditoResultado->cliente->nombre_completo}";
            
            \App\Models\LogActividad::registrar(
                $tipoActividad,
                $mensajeActividad .
                ($request->descuento ? ", Descuento aplicado: S/ " . number_format($request->descuento, 2) : ""),
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $creditoResultado->id_credito,
                    'descuento_aplicado' => $request->descuento ?? 0,
                    'cliente_id' => $creditoResultado->id_cliente,
                ]
            );

            DB::commit();

            $mensaje = ($request->tipo_operacion === 'bajo_cuenta') 
                ? 'Bajo cuenta procesado correctamente. Nuevo crédito creado'
                : 'Datos del crédito actualizados correctamente';

            return response()->json([
                'message' => $mensaje .
                           ($request->descuento ? ' con descuento aplicado' : ''),
                'credito' => $creditoResultado,
                'credito_original_pagado' => ($request->tipo_operacion === 'bajo_cuenta') ? $credito->id_credito : null
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
            $credito = Creditos::findOrFail($request->credito_id ?? $request->id);
            $saldoAnterior = $credito->saldo_actual;

            // Crear abono del saldo actual antes de renovar y crear nuevo crédito
            if ($saldoAnterior > 0) {
                // Buscar o crear el concepto "Abono de Renovación"
                $conceptoRenovacion = Concepto::firstOrCreate(
                    ['nombre' => 'Abono de Renovación'],
                    ['tipo' => 'Ingresos']
                );

                // Crear el abono del saldo actual
                $abono = Abonos::create([
                    'id_credito' => $credito->id_credito,
                    'id_cliente' => $credito->id_cliente,
                    'id_ruta' => $credito->id_ruta,
                    'id_usuario' => auth()->id(),
                    'fecha_pago' => now(),
                    'monto_abono' => $saldoAnterior,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_posterior' => 0,
                    'observaciones' => 'Abono automático por renovación',
                    'id_concepto' => $conceptoRenovacion->id,
                    'estado' => true,
                    'activar_segundo_recorrido' => false,
                ]);

                // Crear el concepto de abono en conceptos_abono
                $abono->conceptosabonos()->create([
                    'id_usuario' => auth()->id(),
                    'tipo_concepto' => 'Abono de Renovación',
                    'monto' => $saldoAnterior,
                    'referencia' => 'Abono automático por renovación',
                    'id_caja' => 1
                ]);

                // Marcar el crédito original como pagado
                $credito->saldo_actual = 0;
                $credito->por_renovar = false;
                $credito->save();
            }

            // Usar los días de pago del formulario (Forma de Pago - Días)
            $diasPago = $request->dias_plazo; // Días de pago del nuevo crédito
            
            // Validar días de pago
            if (!$diasPago || $diasPago <= 0) {
                return response()->json([
                    'message' => 'Los días de pago deben ser mayor a cero.',
                ], 400);
            }

            // Usar el porcentaje de interés del request
            $porcentajeInteres = $request->porcentaje_interes;
            $interesCalculado = ($request->valor_credito * $porcentajeInteres) / 100;
            
            // Calcular cuota diaria basada en los días de pago (valor + interés)
            $cuotaDiaria = round(($request->valor_credito + $interesCalculado) / $diasPago, 2);

            // Usar la fecha editada si se proporciona, sino usar la fecha actual
            $fechaCredito = $request->fecha_credito ? $request->fecha_credito : now();

            // Crear nuevo crédito con los valores de renovación
            $nuevoCredito = Creditos::create([
                'id_cliente' => $credito->id_cliente,
                'id_ruta' => $credito->id_ruta,
                'id_usuario' => auth()->id(),
                'id_concepto' => $credito->id_concepto,
                'forma_pago' => $credito->forma_pago, // Mantener la misma forma de pago del crédito original
                'orden_cobro' => $credito->orden_cobro,
                'fecha_credito' => $fechaCredito,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'fecha_proximo_pago' => now()->addDays($diasPago),
                'valor_credito' => $request->valor_credito,
                'porcentaje_interes' => $porcentajeInteres, // Usar el nuevo interés
                'valor_cuota' => $cuotaDiaria,
                'numero_cuotas' => $diasPago, // Número de cuotas basado en días de pago
                'dias_plazo' => $diasPago, // Días de plazo basado en días de pago
                'saldo_actual' => $request->valor_credito + $interesCalculado,
                'observaciones' => 'Nuevo crédito por renovación del crédito #' . $credito->id_credito,
                'por_renovar' => false,
                'descuento_aplicado' => 0,
                'es_adicional' => false
            ]);

            // Aplicar descuento al nuevo crédito si se proporciona
            if ($request->descuento && $request->descuento > 0) {
                // Validar que el descuento no sea mayor al nuevo saldo
                if ($request->descuento > $nuevoCredito->saldo_actual) {
                    return response()->json([
                        'error' => 'El descuento no puede ser mayor al nuevo saldo'
                    ], 400);
                }
                $nuevoCredito->saldo_actual = $nuevoCredito->saldo_actual - $request->descuento;
                $nuevoCredito->descuento_aplicado = $request->descuento;
                $nuevoCredito->save();
            }

            // Guardar los medios de pago nuevos en el nuevo crédito
            if ($request->has('medios_pago') && is_array($request->medios_pago)) {
                foreach ($request->medios_pago as $mp) {
                    $nuevoCredito->conceptosCredito()->create([
                        'tipo_concepto' => $mp['tipo'],
                        'monto'         => $mp['monto'],
                    ]);
                    
                    // Si es un pago por Yape y se proporciona nombre del cliente, crear registro en YapeCliente solo si no existe
                    if ($mp['tipo'] === 'Yape' && isset($mp['nombre_cliente']) && !empty($mp['nombre_cliente'])) {
                        // Extraer el nombre limpio (sin "(Nuevo)" si existe)
                        $nombreLimpio = str_replace(' (Nuevo)', '', $mp['nombre_cliente']);
                        
                        // Obtener el nombre completo del cliente
                        $cliente = \App\Models\Clientes::find($nuevoCredito->id_cliente);
                        $nombreCompletoCliente = $cliente ? $cliente->nombre_completo : '';
                        
                        // Verificar si el nombre original contenía "(Nuevo)" para forzar creación de nuevo registro
                        $esNuevoRegistro = strpos($mp['nombre_cliente'], '(Nuevo)') !== false;
                        
                        if ($esNuevoRegistro) {
                            // Crear nuevo registro independiente para este crédito
                            \App\Models\YapeCliente::create([
                                'id_cliente' => $nuevoCredito->id_cliente,
                                'id_credito' => $nuevoCredito->id_credito,
                                'nombre' => $nombreLimpio,
                                'user_id' => auth()->id(),
                                'monto' => $mp['monto'],
                                'valor' => $nuevoCredito->valor_credito,
                                'entregar' => 0,
                            ]);
                        } else {
                            // Buscar YapeCliente existente sin id_credito asignado (disponible para actualizar)
                            $yapeClienteDisponible = \App\Models\YapeCliente::where('id_cliente', $nuevoCredito->id_cliente)
                                ->where('nombre', $nombreLimpio)
                                ->whereNull('id_credito')
                                ->first();
                            
                            if ($yapeClienteDisponible) {
                                // Actualizar el registro existente asignándole el nuevo crédito
                                $yapeClienteDisponible->update([
                                    'id_credito' => $nuevoCredito->id_credito,
                                    'monto' => $yapeClienteDisponible->monto + $mp['monto'],
                                    'entregar' => $yapeClienteDisponible->entregar + $mp['monto'],
                                    'valor' => $nuevoCredito->valor_credito
                                ]);
                            } else {
                                // Buscar si ya existe un YapeCliente para este crédito específico
                                $yapeClienteCredito = \App\Models\YapeCliente::where('id_cliente', $nuevoCredito->id_cliente)
                                    ->where('nombre', $nombreLimpio)
                                    ->where('id_credito', $nuevoCredito->id_credito)
                                    ->first();
                                
                                if ($yapeClienteCredito) {
                                    // Actualizar el registro del mismo crédito
                                    $yapeClienteCredito->monto += $mp['monto'];
                                    $yapeClienteCredito->valor = $nuevoCredito->valor_credito;
                                    $yapeClienteCredito->save();
                                } else {
                                    // Crear nuevo registro para este crédito
                                    \App\Models\YapeCliente::create([
                                        'id_cliente' => $nuevoCredito->id_cliente,
                                        'id_credito' => $nuevoCredito->id_credito,
                                        'nombre' => $nombreLimpio,
                                        'user_id' => auth()->id(),
                                        'monto' => $mp['monto'],
                                        'valor' => $nuevoCredito->valor_credito,
                                        'entregar' => 0,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Disparar evento de crédito creado
            event(new CreditoCreated($nuevoCredito));

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Renovación de Crédito',
                "Crédito renovado para cliente: {$nuevoCredito->cliente->nombre_completo}. Nuevo crédito creado: #{$nuevoCredito->id_credito}" .
                ($request->descuento ? ", Descuento aplicado: S/ " . number_format($request->descuento, 2) : ""),
                [
                    'tabla_afectada' => 'creditos',
                    'registro_id' => $nuevoCredito->id_credito,
                    'descuento_aplicado' => $request->descuento ?? 0,
                    'cliente_id' => $nuevoCredito->id_cliente,
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Crédito renovado correctamente. Nuevo crédito creado' .
                           ($request->descuento ? ' con descuento aplicado' : '') . '.',
                'credito' => $nuevoCredito,
                'credito_original_pagado' => $credito->id_credito,
                'cuota_diaria_calculada' => $nuevoCredito->valor_cuota,
                'dias_pago' => $diasPago,
                'interes_calculado' => $interesCalculado,
                'porcentaje_interes' => $porcentajeInteres
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
                'activar_segundo_recorrido' => false, // Agregar este campo para evitar el error de null
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

    public function getYapeCliente($creditoId)
    {
        try {
            $credito = Creditos::findOrFail($creditoId);
            
            // Buscar el YapeCliente asociado al crédito
            $yapeCliente = \App\Models\YapeCliente::where('id_credito', $creditoId)->first();
            
            if ($yapeCliente) {
                return response()->json([
                    'success' => true,
                    'nombre_yape' => $yapeCliente->nombre
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No hay YapeCliente asociado a este crédito'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener YapeCliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener YapeCliente'
            ], 500);
        }
    }

    public function getYapeClienteCompleto($clienteId)
    {
        try {
            // Buscar YapeClientes del mismo cliente que tengan saldo pendiente por yapear
            // Ordenar por fecha de creación descendente para priorizar los más recientes
            $yapeClientes = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                ->whereNotNull('nombre')
                ->where('nombre', '!=', '')
                ->with('abonos')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($yapeClientes as $yapeCliente) {
                // Calcular el yapeado real (abonos - devoluciones)
                $yapeadoReal = 0;
                foreach ($yapeCliente->abonos as $abono) {
                    if ($abono->es_devolucion) {
                        $yapeadoReal -= $abono->monto_abono;
                    } else {
                        $yapeadoReal += $abono->monto_abono;
                    }
                }
                
                // Si el yapeado es menor al monto (aún hay saldo pendiente), retornar este nombre
                if ($yapeadoReal < $yapeCliente->monto) {
                    return response()->json([
                        'success' => true,
                        'nombre_yape' => $yapeCliente->nombre
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No hay YapeCliente con saldo pendiente para este cliente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener YapeCliente con saldo pendiente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener YapeCliente con saldo pendiente'
            ], 500);
        }
    }

    public function getYapeClientes($clienteId)
    {
        try {
            // Obtener solo los YapeClientes del cliente que NO tienen id_credito asignado (disponibles)
            $yapeClientes = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                ->whereNotNull('nombre')
                ->where('nombre', '!=', '')
                ->whereNull('id_credito') // Solo los que no están asignados a un crédito
                ->with('abonos')
                ->orderBy('created_at', 'desc')
                ->get();

            $opcionesYapeCliente = [];
            $clienteConSaldoPendiente = null;
            
            foreach ($yapeClientes as $yapeCliente) {
                // Calcular el yapeado real (abonos - devoluciones)
                $yapeadoReal = 0;
                foreach ($yapeCliente->abonos as $abono) {
                    if ($abono->es_devolucion) {
                        $yapeadoReal -= $abono->monto_abono;
                    } else {
                        $yapeadoReal += $abono->monto_abono;
                    }
                }
                
                $saldoPendiente = $yapeCliente->monto - $yapeadoReal;
                
                // Si tiene saldo pendiente, es el cliente que debe aparecer en verde
                if ($saldoPendiente > 0) {
                    $clienteConSaldoPendiente = [
                        'id' => $yapeCliente->id,
                        'nombre_yape' => $yapeCliente->nombre,
                        'monto_total' => $yapeCliente->monto,
                        'yapeado' => $yapeadoReal,
                        'saldo_pendiente' => $saldoPendiente,
                        'tiene_saldo_pendiente' => true
                    ];
                    break; // Solo necesitamos el primero con saldo pendiente
                }
            }
            
            // Si hay un cliente con saldo pendiente, solo mostrar ese
            if ($clienteConSaldoPendiente) {
                $opcionesYapeCliente[] = $clienteConSaldoPendiente;
            } else {
                // Si no hay cliente con saldo pendiente, agregar opción con nombre del cliente
                $cliente = \App\Models\Clientes::find($clienteId);
                $nombreCliente = $cliente ? $cliente->nombre_completo : 'Cliente';
                
                $opcionesYapeCliente[] = [
                    'id' => 'nuevo',
                    'nombre_yape' => $nombreCliente . ' (Nuevo)',
                    'monto_total' => 0,
                    'yapeado' => 0,
                    'saldo_pendiente' => 0,
                    'tiene_saldo_pendiente' => false
                ];
            }
            
            return response()->json([
                'success' => true,
                'yape_clientes' => $opcionesYapeCliente
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener opciones YapeCliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener opciones YapeCliente'
            ], 500);
        }
    }
}