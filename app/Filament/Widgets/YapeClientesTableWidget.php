<?php

namespace App\Filament\Widgets;

use App\Models\YapeCliente;
use App\Models\Abonos;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class YapeClientesTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    // Propiedad para rastrear si hay filtro activo
    protected static bool $hasActiveFilter = false;

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = YapeCliente::with(['cliente', 'abonos']);

        // Filtrar por ruta desde la sesión si es necesario
        $rutaId = Session::get('selected_ruta_id');
        if ($rutaId) {
            // Si YapeCliente tiene relación con ruta, agregar filtro
            // $query->whereHas('cliente', function($q) use ($rutaId) {
            //     $q->where('id_ruta', $rutaId);
            // });
        }

        return $query;
    }

    protected function getTableFilters(): array
    {
        return [
            // Filtro para mostrar solo pendientes y excesos (por defecto)
            SelectFilter::make('estado_filtro')
                ->label('Mostrar')
                ->options([
                    'pendientes_excesos' => 'Solo Pendientes y Excesos',
                    'todos' => 'Todos los registros',
                ])
                ->default('pendientes_excesos')
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? 'pendientes_excesos';

                    if ($value === 'pendientes_excesos') {
                        return $query->whereColumn('entregar', '!=', 'monto');
                    }

                    return $query; // Mostrar todos
                }),

            // Filtro rápido por fechas predefinidas
            SelectFilter::make('periodo_rapido')
                ->label('Período')
                ->options([
                    'hoy' => 'Hoy',
                    'ayer' => 'Ayer',
                    'esta_semana' => 'Esta semana',
                    'semana_pasada' => 'Semana pasada',
                    'este_mes' => 'Este mes',
                    'mes_pasado' => 'Mes pasado',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    if (!$value) {
                        return $query;
                    }

                    // Aplicar el filtro de fecha correspondiente
                    switch ($value) {
                        case 'hoy':
                            return $query->whereDate('created_at', Carbon::today());
                        case 'ayer':
                            return $query->whereDate('created_at', Carbon::yesterday());
                        case 'esta_semana':
                            return $query->whereBetween('created_at', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek()
                            ]);
                        case 'semana_pasada':
                            return $query->whereBetween('created_at', [
                                Carbon::now()->subWeek()->startOfWeek(),
                                Carbon::now()->subWeek()->endOfWeek()
                            ]);
                        case 'este_mes':
                            return $query->whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year);
                        case 'mes_pasado':
                            return $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                                ->whereYear('created_at', Carbon::now()->subMonth()->year);
                        default:
                            return $query;
                    }
                }),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            // Cliente
            TextColumn::make('cliente.nombre_completo')
                ->label('Cliente')
                ->searchable()
                ->getStateUsing(function (YapeCliente $record) {
                    return $record->cliente ? $record->cliente->nombre_completo : 'Sin cliente asignado';
                }),

            // Nombre Yape - Ahora como enlace clickeable
            TextColumn::make('nombre')
                ->label('Nombre Yape')
                ->searchable()
                ->color('primary')
                ->weight('bold')
                ->action(
                    Action::make('ver_pagos')
                        ->label('Ver Pagos')
                        ->modalHeading(fn (YapeCliente $record) => 'Pagos realizados a: ' . $record->nombre)
                        ->modalContent(function (YapeCliente $record) {
                            // Obtener todos los abonos para este YapeCliente
                            $abonos = Abonos::where('id_yape_cliente', $record->id)
                                ->with(['cliente'])
                                ->orderBy('created_at', 'desc')
                                ->get();

                            $html = '<div class="overflow-x-auto">';
                            $html .= '<table class="min-w-full divide-y divide-gray-200">';
                            $html .= '<thead class="bg-gray-50">';
                            $html .= '<tr>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>';
                            $html .= '</tr>';
                            $html .= '</thead>';
                            $html .= '<tbody class="bg-white divide-y divide-gray-200">';

                            if ($abonos->count() > 0) {
                                foreach ($abonos as $abono) {
                                    $clienteNombre = $abono->cliente ? $abono->cliente->nombre_completo : 'Sin cliente';
                                    $monto = 'S/ ' . number_format($abono->monto_abono, 2);
                                    $fecha = $abono->created_at->format('d/m/Y');

                                    $html .= '<tr>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $clienteNombre . '</td>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $monto . '</td>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $fecha . '</td>';
                                    $html .= '</tr>';
                                }
                            } else {
                                $html .= '<tr>';
                                $html .= '<td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay pagos registrados</td>';
                                $html .= '</tr>';
                            }

                            $html .= '</tbody>';
                            $html .= '</table>';
                            $html .= '</div>';

                            // Mostrar totales
                            $totalPagos = $abonos->sum('monto_abono');
                            $html .= '<div class="mt-4 p-4 bg-gray-50 rounded-lg">';
                            $html .= '<div class="flex justify-between items-center">';
                            $html .= '<span class="text-sm font-medium text-gray-700">Total de Pagos:</span>';
                            $html .= '<span class="text-lg font-bold text-green-600">S/ ' . number_format($totalPagos, 2) . '</span>';
                            $html .= '</div>';
                            $html .= '<div class="flex justify-between items-center mt-2">';
                            $html .= '<span class="text-sm font-medium text-gray-700">Cantidad de Pagos:</span>';
                            $html .= '<span class="text-sm font-semibold text-blue-600">' . $abonos->count() . ' pagos</span>';
                            $html .= '</div>';
                            $html .= '</div>';

                            // Agregar botón de descarga PDF
                            $downloadUrl = route('yape-cliente.pdf', $record->id);
                            $html .= '<div class="mt-4 text-center">';
                            $html .= '<a href="' . $downloadUrl . '" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">';
                            $html .= '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                            $html .= '</svg>';
                            $html .= 'PDF';
                            $html .= '</a>';
                            $html .= '</div>';

                            return new HtmlString($html);
                        })
                        ->modalWidth('4xl')
                ),

            // Cobrador
            TextColumn::make('user.name')
                ->label('Cobrador')
                ->searchable()
                ->getStateUsing(function (YapeCliente $record) {
                    return $record->user ? $record->user->name : 'Sin asignar';
                }),

            // Monto
            TextColumn::make('monto')
                ->label('Monto')
                ->money('PEN', true),

            // Entregar (suma de abonos realizados)
            TextColumn::make('entregar_calculado')
                ->label('Entregar')
                ->getStateUsing(function (YapeCliente $record) {
                    // Sumar todos los abonos que tienen este id_yape_cliente
                    $abonosSum = Abonos::where('id_yape_cliente', $record->id)->sum('monto_abono');

                    // Actualizar el campo entregar en la base de datos
                    $record->update(['entregar' => $abonosSum]);

                    return $abonosSum;
                })
                ->money('PEN', true),

            // Faltante (monto - entregar)
            TextColumn::make('faltante')
                ->label('Faltante')
                ->getStateUsing(function (YapeCliente $record) {
                    $abonosSum = Abonos::where('id_yape_cliente', $record->id)->sum('monto_abono');
                    $faltante = $record->monto - $abonosSum;
                    return max(0, $faltante);
                })
                ->money('PEN', true)
                ->color(function (YapeCliente $record) {
                    $abonosSum = Abonos::where('id_yape_cliente', $record->id)->sum('monto_abono');
                    $faltante = $record->monto - $abonosSum;
                    return $faltante > 0 ? 'warning' : 'success';
                }),

            // Devolución (entregar - monto, solo si es positivo)
            TextColumn::make('devolucion')
                ->label('Devolución')
                ->getStateUsing(function (YapeCliente $record) {
                    $abonosSum = Abonos::where('id_yape_cliente', $record->id)->sum('monto_abono');
                    $devolucion = $abonosSum - $record->monto;
                    return max(0, $devolucion);
                })
                ->money('PEN', true)
                ->color('success'),

                /*
            // Fecha de Registro
            TextColumn::make('created_at')
                ->label('Fecha de Registro')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
                */

            // Estado (usando BadgeColumn en lugar de TextColumn con badge)
            BadgeColumn::make('estado')
                ->label('Estado')
                ->getStateUsing(function (YapeCliente $record) {
                    $abonosSum = Abonos::where('id_yape_cliente', $record->id)->sum('monto_abono');

                    if ($abonosSum == $record->monto) {
                        return 'Completo';
                    } elseif ($abonosSum > $record->monto) {
                        return 'Exceso';
                    } else {
                        return 'Pendiente';
                    }
                })
                ->colors([
                    'success' => 'Completo',
                    'primary' => 'Exceso',
                    'warning' => 'Pendiente',
                ]),
        ];
    }

    protected function getTableHeading(): ?string
    {
        return 'Yape Clientes - Control de Entregas';
    }
}