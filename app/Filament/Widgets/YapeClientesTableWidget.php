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
                        ->requiresConfirmation(false)
                        ->modalHeading(fn (YapeCliente $record) => 'Pagos realizados a: ' . $record->nombre)
                        ->modalContent(function (YapeCliente $record) {
                            // Obtener todos los abonos para este YapeCliente con sus conceptos e imágenes
                            $abonos = Abonos::where('id_yape_cliente', $record->id)
                                ->with(['cliente', 'usuario', 'conceptosabonos'])
                                ->orderBy('created_at', 'desc')
                                ->get();

                            $html = '<div class="space-y-6">';

                            // Tabla de abonos
                            $html .= '<div class="overflow-x-auto">';
                            $html .= '<h3 class="text-lg font-semibold text-gray-900 mb-4">Detalle de Abonos</h3>';
                            $html .= '<table class="min-w-full divide-y divide-gray-200">';
                            $html .= '<thead class="bg-gray-50">';
                            $html .= '<tr>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>';
                            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprobante</th>';
                            $html .= '</tr>';
                            $html .= '</thead>';
                            $html .= '<tbody class="bg-white divide-y divide-gray-200">';

                            if ($abonos->count() > 0) {
                                foreach ($abonos as $abono) {
                                    $clienteNombre = $abono->cliente ? $abono->cliente->nombre_completo : 'Sin cliente';
                                    $monto = 'S/ ' . number_format($abono->monto_abono, 2);
                                    $fecha = $abono->created_at->format('d/m/Y');

                                    // Verificar si tiene comprobante
                                    $tieneComprobante = $abono->conceptosabonos->where('foto_comprobante', '!=', null)->count() > 0;

                                    $html .= '<tr>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $clienteNombre . '</td>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $monto . '</td>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $fecha . '</td>';
                                    $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">';

                                    if ($tieneComprobante) {
                                        // Crear datos para el modal de imágenes usando la misma estructura que AbonosResource
                                        $abonosConImagenes = $abonos->filter(function($a) {
                                            return $a->conceptosabonos->where('foto_comprobante', '!=', null)->count() > 0;
                                        })->map(function($a) {
                                            return [
                                                'id' => $a->id_abono,
                                                'cliente' => $a->cliente ? $a->cliente->nombre_completo : 'Sin cliente',
                                                'fecha' => $a->created_at->format('d/m/Y H:i'),
                                                'monto' => $a->monto_abono,
                                                'usuario' => $a->usuario ? $a->usuario->name : 'Sin usuario',
                                                'metodos' => $a->conceptosabonos->pluck('tipo_concepto')->implode(', '),
                                                'url' => optional($a->conceptosabonos->firstWhere('foto_comprobante', '!=', null))->foto_comprobante
                                                    ? asset('storage/' . $a->conceptosabonos->firstWhere('foto_comprobante', '!=', null)->foto_comprobante)
                                                    : null,
                                            ];
                                        })->values();

                                        $startIndex = $abonosConImagenes->search(fn($a) => $a['id'] == $abono->id_abono);
                                        $jsonData = htmlspecialchars($abonosConImagenes->toJson(), ENT_QUOTES, 'UTF-8');

                                        $html .= '<button onclick="event.stopPropagation(); event.preventDefault(); window.independentModalManager.openModal(\'modal' . $abono->id_abono . '\')" class="inline-flex items-center px-2 py-1 bg-blue-600 border border-transparent rounded text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">';
                                        $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                                        $html .= '</svg>';
                                        $html .= '</button>';

                                        // Modal usando la misma estructura que AbonosResource
                                        $html .= '<div id="modal' . $abono->id_abono . '" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">';
                                        $html .= '<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">';
                                        $html .= '<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="event.stopPropagation(); window.independentModalManager.closeModal(\'modal' . $abono->id_abono . '\')"></div>';
                                        $html .= '<span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>';
                                        $html .= '<div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full" onclick="event.stopPropagation()">';

                                        $html .= '<div x-data="{';
                                        $html .= 'items: ' . $jsonData . ',';
                                        $html .= 'index: ' . $startIndex . ',';
                                        $html .= 'prev() { if (this.index > 0) this.index--; },';
                                        $html .= 'next() { if (this.index < this.items.length - 1) this.index++; }';
                                        $html .= '}" class="space-y-4 p-6">';

                                        // Header
                                        $html .= '<div class="flex justify-between items-center mb-4">';
                                        $html .= '<h3 class="text-lg font-medium text-gray-900">Comprobantes de Pago</h3>';
                                        $html .= '<button onclick="event.stopPropagation(); event.preventDefault(); window.independentModalManager.closeModal(\'modal' . $abono->id_abono . '\')" class="text-gray-400 hover:text-gray-600 focus:outline-none">';
                                        $html .= '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                                        $html .= '</svg>';
                                        $html .= '</button>';
                                        $html .= '</div>';

                                        // Navegación
                                        $html .= '<div class="flex justify-between items-center mb-4 bg-white p-3 rounded-lg shadow">';
                                        $html .= '<button type="button" @click="prev" :disabled="index === 0" class="flex items-center space-x-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition">';
                                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />';
                                        $html .= '</svg>';
                                        $html .= '<span>Anterior</span>';
                                        $html .= '</button>';

                                        $html .= '<span class="text-sm font-semibold text-gray-700">';
                                        $html .= 'Comprobante <span x-text="index+1"></span> de <span x-text="items.length"></span>';
                                        $html .= '</span>';

                                        $html .= '<button type="button" @click="next" :disabled="index === items.length - 1" class="flex items-center space-x-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition">';
                                        $html .= '<span>Siguiente</span>';
                                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />';
                                        $html .= '</svg>';
                                        $html .= '</button>';
                                        $html .= '</div>';

                                        // Información del abono
                                        $html .= '<div class="grid grid-cols-3 gap-2 text-xs p-2 bg-gray-50 rounded">';
                                        $html .= '<div>';
                                        $html .= '<p class="font-medium text-gray-500 mt-2">Usuario</p>';
                                        $html .= '<p x-text="items[index].usuario"></p>';
                                        $html .= '<p class="font-medium text-gray-500">Cliente</p>';
                                        $html .= '<p x-text="items[index].cliente"></p>';
                                        $html .= '</div>';
                                        $html .= '<div>';
                                        $html .= '<p class="font-medium text-gray-500">Fecha</p>';
                                        $html .= '<p x-text="items[index].fecha"></p>';
                                        $html .= '<p class="font-medium text-gray-500 mt-2">Métodos de pago</p>';
                                        $html .= '<p x-text="items[index].metodos"></p>';
                                        $html .= '</div>';
                                        $html .= '<div>';
                                        $html .= '<p class="font-medium text-gray-500">Monto</p>';
                                        $html .= '<p>S/ <span x-text="items[index].monto"></span></p>';
                                        $html .= '</div>';
                                        $html .= '</div>';

                                        // Imagen
                                        $html .= '<template x-if="items[index].url">';
                                        $html .= '<div class="flex justify-center">';
                                        $html .= '<img :src="items[index].url" class="rounded-lg max-h-[290px] max-w-full object-contain cursor-pointer" @click="window.open(items[index].url, \'_blank\')"/>';
                                        $html .= '</div>';
                                        $html .= '</template>';
                                        $html .= '<template x-if="!items[index].url">';
                                        $html .= '<p class="text-center text-gray-400">No hay comprobante disponible</p>';
                                        $html .= '</template>';

                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</div>';
                                    } else {
                                        $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Sin comprobante</span>';
                                    }

                                    $html .= '</td>';
                                    $html .= '</tr>';
                                }
                            } else {
                                $html .= '<tr>';
                                $html .= '<td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay pagos registrados</td>';
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

                            $html .= '</div>';

                           $html .= '<script type="text/javascript">';
                            $html .= 'if (typeof window.independentModalManager === "undefined") {';
                            $html .= '    window.independentModalManager = {';
                            $html .= '        openModals: [],';
                            $html .= '        openModal: function(modalId) {';
                            $html .= '            var modal = document.getElementById(modalId);';
                            $html .= '            if (modal && !this.openModals.includes(modalId)) {';
                            $html .= '                modal.classList.remove("hidden");';
                            $html .= '                this.openModals.push(modalId);';
                            $html .= '                if (this.openModals.length === 1) {';
                            $html .= '                    document.body.classList.add("overflow-hidden");';
                            $html .= '                }';
                            $html .= '            }';
                            $html .= '        },';
                            $html .= '        closeModal: function(modalId) {';
                            $html .= '            var modal = document.getElementById(modalId);';
                            $html .= '            if (modal) {';
                            $html .= '                modal.classList.add("hidden");';
                            $html .= '                var index = this.openModals.indexOf(modalId);';
                            $html .= '                if (index > -1) {';
                            $html .= '                    this.openModals.splice(index, 1);';
                            $html .= '                }';
                            $html .= '                if (this.openModals.length === 0) {';
                            $html .= '                    document.body.classList.remove("overflow-hidden");';
                            $html .= '                }';
                            $html .= '            }';
                            $html .= '        },';
                            $html .= '        closeAllModals: function() {';
                            $html .= '            this.openModals.forEach(function(modalId) {';
                            $html .= '                var modal = document.getElementById(modalId);';
                            $html .= '                if (modal) modal.classList.add("hidden");';
                            $html .= '            });';
                            $html .= '            this.openModals = [];';
                            $html .= '            document.body.classList.remove("overflow-hidden");';
                            $html .= '        }';
                            $html .= '    };';
                            $html .= '}';
                            $html .= '</script>';

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
                ->label('Por Yapear')
                ->money('PEN', true),

            // Entregar (suma de abonos realizados)
            TextColumn::make('entregar_calculado')
                ->label('Yapeado')
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
