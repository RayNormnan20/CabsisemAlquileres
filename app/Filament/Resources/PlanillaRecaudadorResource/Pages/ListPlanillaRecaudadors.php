<?php

namespace App\Filament\Resources\PlanillaRecaudadorResource\Pages;

use App\Filament\Resources\PlanillaRecaudadorResource;
use App\Models\Ruta;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;

class ListPlanillaRecaudadors extends ListRecords
{
    protected static string $resource = PlanillaRecaudadorResource::class;

    public ?string $ordenarPor = 'ruta';
    public ?string $estadoCredito = 'todos';
    public ?string $rutaId = null;
    public bool $filtrosValidos = true;

    public function mount(): void
    {
        parent::mount();
        
        // Verificar si hay una ruta seleccionada en la sesión
        if (!Session::has('selected_ruta_id')) {
            // Si no hay, cargar la primera ruta activa del usuario
            $user = auth()->user();
            $ruta = $user->rutas()->where('activa', true)->first();
            
            if ($ruta) {
                Session::put('selected_ruta_id', $ruta->id_ruta);
                Session::put('selected_ruta_name', $ruta->nombre_completo ?? $ruta->nombre);
                
                // Disparar notificación para el usuario
                $this->notify('success', 'Ruta seleccionada: ' . ($ruta->nombre_completo ?? $ruta->nombre));
                
                // Forzar recarga de los widgets
                $this->redirect(request()->url());
            }
        }
        
        // Establecer rutaId desde la sesión
        $this->rutaId = Session::get('selected_ruta_id');
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(), // Ocultar creación ya que es una vista
        ];
    }

    protected function getHeader(): View
    {
        return view('filament.resources.planilla-recaudador-resource.header', [
            'rutas' => Ruta::all(),
            'ordenarPor' => $this->ordenarPor,
            'estadoCredito' => $this->estadoCredito, // Corregido: estaba como $this->estadoCredito
            'rutaId' => $this->rutaId,
            'filtrosValidos' => $this->filtrosValidos
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getExtraBodyAttributes(): array
    {
        return [
            'style' => 'background: white;'
        ];
    }

    protected function getFooter(): View
    {
        // Crear consulta base sin filtro de tipo de crédito para mostrar ambas tablas
        $queryBase = parent::getTableQuery()
            ->when($this->estadoCredito === 'activos', function (Builder $query) {
                return $query->where('saldo_actual', '>', 0);
            })
            ->when($this->estadoCredito === 'cancelados', function (Builder $query) {
                return $query->where('saldo_actual', '<=', 0);
            })
            // Removemos el filtro de 'adicionales' para mostrar ambos tipos
            ->when($this->rutaId, function (Builder $query) {
                return $query->where('id_ruta', $this->rutaId);
            });

        // Aplicar ordenamiento
        switch ($this->ordenarPor) {
            case 'ruta':
                $queryBase->orderBy('ruta')->orderBy('cliente_completo');
                break;
            case 'fecha':
                $queryBase->orderBy('fecha_proximo_pago', 'desc');
                break;
            case 'nombre':
                $queryBase->orderBy('cliente_completo');
                break;
        }
        
        // Créditos regulares (no adicionales)
        $creditosRegulares = (clone $queryBase)->where('es_adicional', 0)->get();
        
        // Créditos adicionales
        $creditosAdicionales = (clone $queryBase)->where('es_adicional', 1)->get();
        
        // Calcular totales
        $totalesRegulares = [
            'credito' => $creditosRegulares->sum('valor_credito'),
            'abonos' => $creditosRegulares->sum('total_abonos'),
            'saldo' => $creditosRegulares->sum('saldo_actual'),
            'cuota' => $creditosRegulares->sum('valor_cuota')
        ];
        
        $totalesAdicionales = [
            'credito' => $creditosAdicionales->sum('valor_credito'),
            'abonos' => $creditosAdicionales->sum('total_abonos'),
            'saldo' => $creditosAdicionales->sum('saldo_actual'),
            'cuota' => $creditosAdicionales->sum('porcentaje_interes')
        ];
        
        $totalGeneral = [
            'credito' => $totalesRegulares['credito'] + $totalesAdicionales['credito'],
            'abonos' => $totalesRegulares['abonos'] + $totalesAdicionales['abonos'],
            'saldo' => $totalesRegulares['saldo'] + $totalesAdicionales['saldo'],
            'cuota' => $totalesRegulares['cuota'] + $totalesAdicionales['cuota']
        ];
        
        return view('filament.resources.planilla-recaudador-resource.footer', [
            'creditosRegulares' => $creditosRegulares,
            'creditosAdicionales' => $creditosAdicionales,
            'totalesRegulares' => $totalesRegulares,
            'totalesAdicionales' => $totalesAdicionales,
            'totalGeneral' => $totalGeneral
        ]);
    }

   protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()
            ->when($this->estadoCredito === 'activos', function (Builder $query) {
                return $query->where('saldo_actual', '>', 0);
            })
            ->when($this->estadoCredito === 'cancelados', function (Builder $query) {
                return $query->where('saldo_actual', '<=', 0);
            })
            ->when($this->estadoCredito === 'adicionales', function (Builder $query) {
                return $query->where('es_adicional', 1);
            })
            ->when($this->rutaId, function (Builder $query) {
                return $query->where('id_ruta', $this->rutaId);
            });

        // Ordenamiento usando los nombres correctos de columnas
        switch ($this->ordenarPor) {
            case 'ruta':
                $query->orderBy('ruta')->orderBy('cliente_completo');
                break;
            case 'fecha':
                $query->orderBy('fecha_proximo_pago', 'desc');
                break;
            case 'nombre':
                $query->orderBy('cliente_completo');
                break;
        }

        return $query;
    }

    // Ocultar la tabla principal de Filament
    protected function shouldPersistTableFiltersInSession(): bool
    {
        return false;
    }

    public function getTableRecords(): \Illuminate\Contracts\Pagination\Paginator
    {
        // Retornar una colección vacía para ocultar la tabla principal
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect([]),
            0,
            10,
            1
        );
    }

    protected function getTableHeading(): ?string
    {
        return null; // Ocultar el encabezado de la tabla principal
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return null; // Ocultar mensaje de estado vacío
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return null; // Ocultar descripción de estado vacío
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return null; // Ocultar icono de estado vacío
    }

    protected function getTableEmptyStateActions(): array
    {
        return []; // Ocultar acciones de estado vacío
    }

    public function getTableContentGrid(): ?array
    {
        return null; // Ocultar grid de contenido
    }

    protected function getTableFilters(): array
    {
        return []; // Ocultar filtros de tabla
    }

    protected function getTableActions(): array
    {
        return []; // Ocultar acciones de tabla
    }

    protected function getTableBulkActions(): array
    {
        return []; // Ocultar acciones en lote
    }

    protected function getEmptyTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Retornar una consulta vacía para ocultar completamente la tabla
        return parent::getTableQuery()->whereRaw('1 = 0');
    }

    public function validarFiltros()
    {
        $this->filtrosValidos = true;
        // Puedes agregar validaciones adicionales aquí si es necesario
    }


   public function exportToPDF()
{
    try {
        // Usar la misma lógica que en getFooter() para obtener los datos de ambas tablas
        $queryBase = parent::getTableQuery()
            ->when($this->estadoCredito === 'activos', function (Builder $query) {
                return $query->where('saldo_actual', '>', 0);
            })
            ->when($this->estadoCredito === 'cancelados', function (Builder $query) {
                return $query->where('saldo_actual', '<=', 0);
            })
            ->when($this->rutaId, function (Builder $query) {
                return $query->where('id_ruta', $this->rutaId);
            });

        // Aplicar ordenamiento
        switch ($this->ordenarPor) {
            case 'ruta':
                $queryBase->orderBy('ruta')->orderBy('cliente_completo');
                break;
            case 'fecha':
                $queryBase->orderBy('fecha_proximo_pago', 'desc');
                break;
            case 'nombre':
                $queryBase->orderBy('cliente_completo');
                break;
        }
        
        // Créditos regulares (no adicionales)
        $creditosRegulares = (clone $queryBase)->where('es_adicional', 0)->get();
        
        // Créditos adicionales
        $creditosAdicionales = (clone $queryBase)->where('es_adicional', 1)->get();

        if ($creditosRegulares->isEmpty() && $creditosAdicionales->isEmpty()) {
            $this->notify('warning', 'No hay datos para exportar');
            return;
        }

        $rutaNombre = Ruta::find($this->rutaId)->nombre ?? 'Todas las rutas';

        Log::info('Generando PDF para la ruta: ' . $rutaNombre, [
            'creditos_regulares' => $creditosRegulares->count(),
            'creditos_adicionales' => $creditosAdicionales->count(),
            'filtros' => [
                'ruta' => $this->rutaId,
                'orden' => $this->ordenarPor,
                'estado' => $this->estadoCredito
            ]
        ]);

        // Calcular totales generales
        $totalGeneral = [
            'credito' => $creditosRegulares->sum('valor_credito') + $creditosAdicionales->sum('valor_credito'),
            'abonos' => $creditosRegulares->sum('total_abonos') + $creditosAdicionales->sum('total_abonos'),
            'saldo' => $creditosRegulares->sum('saldo_actual') + $creditosAdicionales->sum('saldo_actual'),
            'cuota' => $creditosRegulares->sum('valor_cuota') + $creditosAdicionales->sum('valor_cuota')
        ];

        $pdf = Pdf::loadView('filament.resources.pdf.planilla-recaudador', [
            'creditosRegulares' => $creditosRegulares,
            'creditosAdicionales' => $creditosAdicionales,
            'totalGeneral' => $totalGeneral,
            'rutaNombre' => $rutaNombre,
            'fecha' => now()->format('d/m/Y'),
            'orden' => $this->ordenarPor,
            'estadoCredito' => $this->estadoCredito
        ]);

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            'planilla-recaudador-'.now()->format('Y-m-d').'.pdf'
        );

    } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            $this->notify('danger', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    public static function handleRenovacionAction()
    {
        $request = request();
        
        if ($request->isMethod('POST') && $request->has('action') && $request->has('credito_id')) {
            $action = $request->input('action');
            $creditoId = $request->input('credito_id');
            
            $credito = \App\Models\Creditos::find($creditoId);
            
            if ($credito) {
                if ($action === 'habilitar_renovacion') {
                    $credito->por_renovar = true;
                    $credito->save();
                    
                    session()->flash('success', 'Crédito habilitado para renovación');
                } elseif ($action === 'deshabilitar_renovacion') {
                    $credito->por_renovar = false;
                    $credito->save();
                    
                    session()->flash('success', 'Crédito deshabilitado para renovación');
                }
            }
        }
        
        return redirect()->back();
    }

}