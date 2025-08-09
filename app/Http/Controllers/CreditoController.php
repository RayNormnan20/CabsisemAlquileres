<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ConceptoCredito;
use App\Models\Creditos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            ]);

            $credito = Creditos::find($request->credito_id);

            if (!$credito) {
                return response()->json(['error' => 'Crédito no encontrado'], 404);
            }

            $credito->porcentaje_interes = $request->nuevo_interes;
            $credito->dias_plazo = $request->forma_pago;
            $credito->valor_cuota = $request->valor_cuota;
            $credito->saldo_actual = $request->nueva_cuenta;
            $credito->fecha_vencimiento = $request->fecha_vencimiento;

            $credito->save();

            return response()->json([
                'message' => 'Datos del crédito actualizados correctamente',
                'credito' => $credito
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error al actualizar crédito',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /* public function renovar(Request $request)
    {
        Log::info('Llamada al método renovar');

        $data = $request->json()->all(); // ✅ Lee el JSON enviado por fetch
        Log::info('Datos recibidos:', $data);

        DB::beginTransaction();

        try {
            // Buscar el crédito original
            $credito = Creditos::findOrFail($data['id']);

            // Actualizar datos del crédito
            $credito->update([
                'valor_credito'       => $data['valor_credito'],
                'porcentaje_interes'  => $data['porcentaje_interes'],
                'saldo_actual'        => $data['valor_credito'],
                'forma_pago'          => null,
                'dias_plazo'          => $data['dias_plazo'],
                'fecha_credito'       => now(),
                'fecha_vencimiento'   => $data['fecha_vencimiento'],
                'valor_cuota'         => $data['valor_cuota'] ?? 0,
                'numero_cuotas'       => $data['numero_cuotas'] ?? 0,
            ]);

            // Insertar métodos de pago en conceptos_credito
            foreach ($data['medios_pago'] as $medio) {
                ConceptoCredito::create([
                    'id_credito'     => $credito->id_credito,
                    'tipo_concepto'  => $medio['tipo'],   // ejemplo: 'Yape', 'Efectivo'
                    'monto'          => $medio['monto'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Crédito renovado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al renovar crédito: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error al renovar crédito.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    } */

    public function renovar(Request $request)
    {
        try {
            // Buscar el crédito original
            $credito = Creditos::findOrFail($request->id);

            // Actualizar solo los campos permitidos (excluyendo forma_pago)
            $credito->valor_credito = $request->valor_credito;
            $credito->saldo_actual = $request->valor_credito;
            /* $credito->valor_cuota = $request->valor_cuota; */
            $credito->numero_cuotas = $request->numero_cuotas;
            $credito->dias_plazo = $request->dias_plazo;
            $credito->porcentaje_interes = $request->porcentaje_interes;
            $credito->fecha_vencimiento = $request->fecha_vencimiento;
            $credito->fecha_credito = now(); // o el valor que corresponda

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

            return response()->json([
                'message' => 'Crédito renovado correctamente.',
                'cuota_diaria_calculada' => $cuotaDiaria,
                'dias_restantes' => $diasRestantes
            ], 200);
        } catch (\Exception $e) {
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

}
