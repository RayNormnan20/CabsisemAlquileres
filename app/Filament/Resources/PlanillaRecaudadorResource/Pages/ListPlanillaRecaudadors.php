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
        // Establecer ruta por defecto si es necesario
        $this->rutaId = Ruta::first()?->id_ruta; // Cambiado a id_ruta
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

    public function validarFiltros()
    {
        $this->filtrosValidos = true;
        // Puedes agregar validaciones adicionales aquí si es necesario
    }


   public function exportToPDF()
{
    try {
        $query = $this->getTableQuery();
        $records = $query->get();

        if ($records->isEmpty()) {
            $this->notify('warning', 'No hay datos para exportar');
            return;
        }

        $rutaNombre = Ruta::find($this->rutaId)->nombre ?? 'Todas las rutas';

        Log::info('Generando PDF para la ruta: ' . $rutaNombre, [
            'registros' => $records->count(),
            'filtros' => [
                'ruta' => $this->rutaId,
                'orden' => $this->ordenarPor,
                'estado' => $this->estadoCredito
            ]
        ]);

        $pdf = Pdf::loadView('filament.resources.pdf.planilla-recaudador', [
            'records' => $records,
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
}