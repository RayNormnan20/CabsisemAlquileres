<?php

namespace App\Filament\Widgets;

use App\Models\YapeCliente;
use App\Models\Abonos;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;

class YapeUsuariosWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('Listar Usuarios Que Abonaron A Yapes');
    }

    // Propiedades para el filtro de período
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'hoy';
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
                $this->fechaDesde = Carbon::today()->format('Y-m-d');
                $this->fechaHasta = Carbon::today()->format('Y-m-d');
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
        $this->periodoSeleccionado = 'hoy';
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
        return view('filament.widgets.yape-usuarios-header', [
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'fechasValidas' => $this->fechasValidas,
            'periodoSeleccionado' => $this->periodoSeleccionado
        ]);
    }

    protected function getTableQuery(): Builder
    {
        $this->validarFechas();

        if (!$this->fechasValidas) {
            return Abonos::query()->whereRaw('1=0');
        }

        // Si no hay fechas seleccionadas, mostrar todos los datos
        if (!$this->fechaDesde && !$this->fechaHasta) {
            // Usar la misma lógica que abajo pero sin filtros de fecha
        }

        // Crear consulta directa con DB::table para evitar conflictos con el modelo
        $query = DB::table('abonos as a')
            ->join('yape_clientes as yc', 'yc.id', '=', 'a.id_yape_cliente')
            ->join('users as u', 'u.id', '=', 'a.id_usuario')
            ->whereNotNull('yc.nombre')
            ->where('yc.nombre', '!=', '')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY a.id_yape_cliente, a.id_usuario) as id, a.id_yape_cliente, a.id_usuario, yc.nombre as yape_nombre, u.name as usuario_nombre, SUM(CAST(a.monto_abono AS DECIMAL(10,2))) as total_abonado, COUNT(*) as cantidad_pagos, MAX(a.created_at) as ultima_fecha, MAX(a.created_at) as created_at, MAX(a.updated_at) as updated_at')
            ->groupBy('a.id_yape_cliente', 'a.id_usuario', 'yc.nombre', 'u.name');

        // Aplicar filtros de fecha
        if ($this->fechaDesde) {
            $query->whereDate('a.created_at', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $query->whereDate('a.created_at', '<=', $this->fechaHasta);
        }

        // Crear un modelo temporal para compatibilidad con Filament
        $tempModel = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'temp_yape_usuarios';
            protected $primaryKey = 'id';
            public $timestamps = true;
            protected $fillable = ['id', 'id_yape_cliente', 'id_usuario', 'yape_nombre', 'usuario_nombre', 'total_abonado', 'cantidad_pagos', 'ultima_fecha'];
        };

        return $tempModel->newQuery()->fromSub($query, 'grouped_data');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('yape_nombre')
                ->label('NOMBRE YAPE')
                ->searchable()
                ->sortable(),

            TextColumn::make('usuario_nombre')
                ->label('USUARIO')
                ->searchable()
                ->sortable(),

            TextColumn::make('total_abonado')
                ->label('TOTAL')
                ->money('PEN', true)
                ->sortable(),

            TextColumn::make('cantidad_pagos')
                ->label('CANT. PAGOS')
                ->sortable()
                ->getStateUsing(function ($record) {
                    return $record->cantidad_pagos . ' pago' . ($record->cantidad_pagos > 1 ? 's' : '');
                }),

            TextColumn::make('ultima_fecha')
                ->label('ÚLTIMO PAGO')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('id_yape_cliente')
                ->label('Filtrar por Nombre Yape')
                ->options(function () {
                    $options = ['todos' => 'lISTAR TODOS'];
                    $yapeOptions = YapeCliente::whereNotNull('nombre')
                        ->where('nombre', '!=', '')
                        ->pluck('nombre', 'id')
                        ->unique()
                        ->sort();
                    return $options + $yapeOptions->toArray();
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['value']) && $data['value'] !== 'todos' && $data['value'] !== null) {
                        return $query->where('id_yape_cliente', $data['value']);
                    }
                    return $query;
                })
                ->searchable()
                ->placeholder('Seleccionar nombre Yape'),

            SelectFilter::make('id_usuario')
                ->label('Filtrar por Usuario')
                ->options(function () {
                    return \App\Models\User::pluck('name', 'id')
                        ->sort();
                })
                ->searchable()
                ->placeholder('Seleccionar usuario'),
        ];
    }

    protected function getTableHeading(): ?string
    {
        return 'USUARIOS QUE ABONARON A YAPE';
    }

    public function getTableRecordKey($record): string
    {
        return (string) ($record->id_yape_cliente . '_' . $record->id_usuario);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 10;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25, 50];
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'ultima_fecha';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }
}