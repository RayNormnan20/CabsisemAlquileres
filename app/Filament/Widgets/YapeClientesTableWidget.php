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
use Livewire\Component;

class YapeClientesTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    
    // Propiedades para el filtro de período
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'mes_actual';
    public bool $fechasValidas = true;
    
    public function mount(): void
    {
        parent::mount();
        $this->aplicarPeriodo();
    }
    
    public function aplicarPeriodo(): void
    {
        switch ($this->periodoSeleccionado) {
            case 'hoy':
                $this->fechaDesde = Carbon::today()->format('Y-m-d');
                $this->fechaHasta = Carbon::today()->format('Y-m-d');
                break;
            case 'ayer':
                $this->fechaDesde = Carbon::yesterday()->format('Y-m-d');
                $this->fechaHasta = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'esta_semana':
                $this->fechaDesde = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'semana_pasada':
                $this->fechaDesde = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes_actual':
                $this->fechaDesde = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_pasado':
                $this->fechaDesde = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'ultimos_7_dias':
                $this->fechaDesde = Carbon::now()->subDays(6)->format('Y-m-d');
                $this->fechaHasta = Carbon::today()->format('Y-m-d');
                break;
            case 'ultimos_30_dias':
                $this->fechaDesde = Carbon::now()->subDays(29)->format('Y-m-d');
                $this->fechaHasta = Carbon::today()->format('Y-m-d');
                break;
            case 'personalizado':
                // No cambiar las fechas, mantener las que el usuario ha seleccionado
                break;
            default:
                $this->fechaDesde = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
        }
    }
    
    public function validarFechas(): void
    {
        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaDesde = Carbon::parse($this->fechaDesde);
            $fechaHasta = Carbon::parse($this->fechaHasta);
            
            if ($fechaDesde->gt($fechaHasta)) {
                $this->fechasValidas = false;
                return;
            }
        }
        
        $this->fechasValidas = true;
    }
    
    public function updatedPeriodoSeleccionado(): void
     {
         $this->aplicarPeriodo();
     }
     
     public function limpiarFiltros(): void
     {
         $this->fechaDesde = null;
         $this->fechaHasta = null;
         $this->periodoSeleccionado = 'mes_actual';
         $this->aplicarPeriodo();
         $this->fechasValidas = true;
     }
     
     public function updated($name): void
     {
         if (in_array($name, ['fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
             if ($name === 'periodoSeleccionado') {
                 $this->aplicarPeriodo();
             }
         }
     }
     
     protected function getTableHeader(): ?\Illuminate\Contracts\View\View
     {
         return view('filament.widgets.yape-clientes-header', [
             'fechaDesde' => $this->fechaDesde,
             'fechaHasta' => $this->fechaHasta,
             'fechasValidas' => $this->fechasValidas,
             'periodoSeleccionado' => $this->periodoSeleccionado
         ]);
     }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $this->validarFechas();
        
        if (!$this->fechasValidas) {
            return YapeCliente::with(['cliente', 'abonos'])->whereRaw('1=0');
        }
        
        $query = YapeCliente::with(['cliente', 'abonos']);

        // Aplicar filtros de fecha
        $query->when($this->fechaDesde, function (Builder $query) {
            return $query->whereDate('created_at', '>=', $this->fechaDesde);
        })
        ->when($this->fechaHasta, function (Builder $query) {
            return $query->whereDate('created_at', '<=', $this->fechaHasta);
        });

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
                    'completados' => 'Solo Completados',
                    'todos' => 'Todos los registros',
                ])
                ->default('pendientes_excesos')
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? 'pendientes_excesos';

                    if ($value === 'pendientes_excesos') {
                        // Filtrar solo registros que no están completos
                        return $query->whereHas('abonos', function($q) {
                            // Tiene abonos pero no está completo
                        }, '>=', 0)->where(function($subQuery) {
                            $subQuery->whereRaw('(
                                SELECT COALESCE(SUM(monto_abono), 0) 
                                FROM abonos 
                                WHERE abonos.id_yape_cliente = yape_clientes.id
                            ) != yape_clientes.monto');
                        });
                    } elseif ($value === 'completados') {
                        // Filtrar solo registros completados
                        return $query->whereRaw('(
                            SELECT COALESCE(SUM(monto_abono), 0) 
                            FROM abonos 
                            WHERE abonos.id_yape_cliente = yape_clientes.id
                        ) = yape_clientes.monto');
                    }

                    return $query; // Mostrar todos
                }),

            // Filtro de rango de fechas personalizado
            Filter::make('fecha_rango')
                ->form([
                    DatePicker::make('desde')
                        ->label('Fecha Desde')
                        ->placeholder('Seleccionar fecha desde')
                        ->reactive()
                        ->afterStateUpdated(function ($state, $livewire) {
                            $livewire->fechaDesde = $state;
                            $livewire->periodoSeleccionado = 'personalizado';
                        }),
                    DatePicker::make('hasta')
                        ->label('Fecha Hasta')
                        ->placeholder('Seleccionar fecha hasta')
                        ->reactive()
                        ->afterStateUpdated(function ($state, $livewire) {
                            $livewire->fechaHasta = $state;
                            $livewire->periodoSeleccionado = 'personalizado';
                        }),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['desde'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['hasta'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
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
                ->tooltip('Ver detalles')
                ->action(
                    Action::make('ver_pagos')
                        ->label('Ver detalles')
                        ->requiresConfirmation(false)
                        ->modalHeading(fn (YapeCliente $record) => 'Pagos realizados a: ' . $record->nombre)
                        ->modalContent(function (YapeCliente $record) {
                            // Obtener todos los abonos para este YapeCliente con sus conceptos e imágenes
                            $abonos = Abonos::where('id_yape_cliente', $record->id)
                                ->with(['cliente', 'usuario', 'conceptosabonos'])
                                ->orderBy('created_at', 'desc')
                                ->get();

                            $html = '<div class="space-y-4 sm:space-y-6">';

                            // Tabla de abonos
                            $html .= '<div class="overflow-x-auto">';
                            $html .= '<h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Detalle de Abonos</h3>';
                            $html .= '<div class="max-h-80 sm:max-h-96 overflow-y-auto border border-gray-200 rounded-lg">';
                            $html .= '<table class="min-w-full divide-y divide-gray-200">';
                            $html .= '<thead class="bg-gray-50">';
                            $html .= '<tr>';
                            $html .= '<th class="px-2 py-2 sm:px-6 sm:py-3 lg:px-8 lg:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>';
                            $html .= '<th class="px-2 py-2 sm:px-6 sm:py-3 lg:px-8 lg:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>';
                            $html .= '<th class="px-2 py-2 sm:px-6 sm:py-3 lg:px-8 lg:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Fecha y Hora</th>';
                            $html .= '<th class="px-2 py-2 sm:px-6 sm:py-3 lg:px-8 lg:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ver</th>';
                            $html .= '</tr>';
                            $html .= '</thead>';
                            $html .= '<tbody class="bg-white divide-y divide-gray-200">';

                            if ($abonos->count() > 0) {
                                foreach ($abonos as $abono) {
                                    $clienteNombre = $abono->cliente ? $abono->cliente->nombre_completo : 'Sin cliente';
                                    $monto = 'S/ ' . number_format($abono->monto_abono, 2);
                                    $fecha = $abono->created_at->format('d/m/Y');
                                    $fechaHora = $abono->created_at->format('d/m/Y H:i');

                                    // Verificar si tiene comprobante
                                    $tieneComprobante = $abono->conceptosabonos->where('foto_comprobante', '!=', null)->count() > 0;

                                    $html .= '<tr>';
                                    $html .= '<td class="px-2 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5 text-xs sm:text-sm lg:text-base text-gray-900">';
                                    $html .= '<div class="font-medium lg:whitespace-nowrap lg:overflow-visible">' . $clienteNombre . '</div>';
                                    $html .= '<div class="text-gray-500 sm:hidden text-xs">' . $fechaHora . '</div>';
                                    $html .= '</td>';
                                    $html .= '<td class="px-2 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5 whitespace-nowrap text-xs sm:text-sm lg:text-base font-semibold text-green-600">' . $monto . '</td>';
                                    $html .= '<td class="px-2 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5 whitespace-nowrap text-xs sm:text-sm lg:text-base text-gray-900 hidden sm:table-cell">' . $fechaHora . '</td>';
                                    $html .= '<td class="px-2 py-3 sm:px-6 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">';

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
                                        $html .= '<div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full w-full max-w-sm sm:max-w-4xl mx-4 sm:mx-auto" onclick="event.stopPropagation()">';

                                        $html .= '<div x-data="{';
                                        $html .= 'items: ' . $jsonData . ',';
                                        $html .= 'index: ' . $startIndex . ',';
                                        $html .= 'prev() { if (this.index > 0) this.index--; },';
                                        $html .= 'next() { if (this.index < this.items.length - 1) this.index++; }';
                                        $html .= '}" class="space-y-3 p-3 sm:p-6 sm:space-y-4">';

                                        // Header
                                        $html .= '<div class="flex justify-between items-center mb-3 sm:mb-4">';
                                        $html .= '<h3 class="text-base sm:text-lg font-medium text-gray-900">Comprobantes de Pago</h3>';
                                        $html .= '<button onclick="event.stopPropagation(); event.preventDefault(); window.independentModalManager.closeModal(\'modal' . $abono->id_abono . '\')" class="text-gray-400 hover:text-gray-600 focus:outline-none">';
                                        $html .= '<svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                                        $html .= '</svg>';
                                        $html .= '</button>';
                                        $html .= '</div>';

                                        // Navegación
                                        $html .= '<div class="flex justify-between items-center mb-3 sm:mb-4 bg-white p-2 sm:p-3 rounded-lg shadow">';
                                        $html .= '<button type="button" @click="prev" :disabled="index === 0" class="flex items-center space-x-1 px-2 py-1 sm:px-4 sm:py-2 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition">';
                                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />';
                                        $html .= '</svg>';
                                        $html .= '<span class="hidden sm:inline">Anterior</span>';
                                        $html .= '</button>';

                                        $html .= '<span class="text-xs sm:text-sm font-semibold text-gray-700 text-center">';
                                        $html .= '<span x-text="index+1"></span>/<span x-text="items.length"></span>';
                                        $html .= '</span>';

                                        $html .= '<button type="button" @click="next" :disabled="index === items.length - 1" class="flex items-center space-x-1 px-2 py-1 sm:px-4 sm:py-2 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition">';
                                        $html .= '<span class="hidden sm:inline">Siguiente</span>';
                                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />';
                                        $html .= '</svg>';
                                        $html .= '</button>';
                                        $html .= '</div>';

                                        // Información del abono
                                        $html .= '<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 text-xs sm:text-sm p-3 bg-gray-50 rounded">';
                                        $html .= '<div class="space-y-1">';
                                        $html .= '<p class="font-medium text-gray-500">Usuario</p>';
                                        $html .= '<p class="text-gray-900 font-semibold" x-text="items[index].usuario"></p>';
                                        $html .= '</div>';
                                        $html .= '<div class="space-y-1">';
                                        $html .= '<p class="font-medium text-gray-500">Cliente</p>';
                                        $html .= '<p class="text-gray-900 font-semibold" x-text="items[index].cliente"></p>';
                                        $html .= '</div>';
                                        $html .= '<div class="space-y-1">';
                                        $html .= '<p class="font-medium text-gray-500">Fecha</p>';
                                        $html .= '<p class="text-gray-900 font-semibold" x-text="items[index].fecha"></p>';
                                        $html .= '</div>';
                                        $html .= '<div class="space-y-1 sm:col-span-2">';
                                        $html .= '<p class="font-medium text-gray-500">Métodos de pago</p>';
                                        $html .= '<p class="text-gray-900 font-semibold" x-text="items[index].metodos"></p>';
                                        $html .= '</div>';
                                        $html .= '<div class="space-y-1">';
                                        $html .= '<p class="font-medium text-gray-500">Monto</p>';
                                        $html .= '<p class="text-green-600 font-bold text-lg">S/ <span x-text="items[index].monto"></span></p>';
                                        $html .= '</div>';
                                        $html .= '</div>';

                                        // Imagen
                                        $html .= '<template x-if="items[index].url">';
                                        $html .= '<div class="flex justify-center bg-gray-100 rounded-lg p-2">';
                                        $html .= '<img :src="items[index].url" class="rounded-lg max-h-[250px] sm:max-h-[350px] max-w-full object-contain cursor-pointer shadow-lg" @click="window.open(items[index].url, \'_blank\')" />';
                                        $html .= '</div>';
                                        $html .= '</template>';
                                        $html .= '<template x-if="!items[index].url">';
                                        $html .= '<div class="text-center py-8 bg-gray-100 rounded-lg">';
                                        $html .= '<p class="text-gray-400 text-sm">No hay comprobante disponible</p>';
                                        $html .= '</div>';
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
                            $html .= '</div>';

                            // Mostrar totales
                            $totalPagos = $abonos->sum('monto_abono');
                            $devolucion = max(0, $totalPagos - $record->monto);
                            $html .= '<div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-gray-50 rounded-lg">';
                            $html .= '<div class="flex justify-between items-center">';
                            $html .= '<span class="text-xs sm:text-sm font-medium text-gray-700">Total de Pagos:</span>';
                            $html .= '<span class="text-base sm:text-lg font-bold text-green-600">S/ ' . number_format($totalPagos, 2) . '</span>';
                            $html .= '</div>';
                            if ($devolucion > 0) {
                                $html .= '<div class="flex justify-between items-center mt-2">';
                                $html .= '<span class="text-xs sm:text-sm font-medium text-gray-700">Devolución:</span>';
                                $html .= '<span class="text-base sm:text-lg font-bold text-red-600">S/ ' . number_format($devolucion, 2) . '</span>';
                                $html .= '</div>';
                            }
                            $html .= '<div class="flex justify-between items-center mt-2">';
                            $html .= '<span class="text-xs sm:text-sm font-medium text-gray-700">Cantidad de Pagos:</span>';
                            $html .= '<span class="text-xs sm:text-sm font-semibold text-blue-600">' . $abonos->count() . ' pagos</span>';
                            $html .= '</div>';
                            $html .= '</div>';

                            // Agregar botón de descarga PDF
                            $downloadUrl = route('yape-cliente.pdf', $record->id);
                            $html .= '<div class="mt-3 sm:mt-4 text-center">';
                            $html .= '<a href="' . $downloadUrl . '" target="_blank" class="inline-flex items-center justify-center w-full sm:w-auto px-3 py-2 sm:px-4 sm:py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">';
                            $html .= '<svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
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
                // Valor (Préstamo)
            TextColumn::make('valor')
                ->label('Préstamo')
                ->money('PEN', true)
                ->sortable(),

            // Monto
            TextColumn::make('monto')
                ->label('Por Yapear')
                ->money('PEN', true),

            // Yapeado (suma de abonos realizados)
            TextColumn::make('yapeado_calculado')
                ->label('Yapeado')
                ->getStateUsing(function (YapeCliente $record) {
                    // Sumar todos los abonos que tienen este id_yape_cliente
                    return Abonos::where('id_yape_cliente', $record->id)->sum('monto_abono');
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

            // Devolución (yapeado - monto del crédito, solo si es positivo)
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
         return null; // El título se muestra en el header personalizado
     }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 25;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100, 'all'];
    }
}
