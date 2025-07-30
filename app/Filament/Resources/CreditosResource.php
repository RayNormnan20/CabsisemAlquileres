<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditosResource\Pages;
use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\OrdenCobro;
use App\Models\TipoPago;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Html;
use Filament\Forms\Components\Image as ImageComponent;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Livewire\TemporaryUploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class CreditosResource extends Resource
{
    protected static ?string $model = Creditos::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?int $navigationSort = 3;


    protected static function getNavigationLabel(): string
    {
        return __('Listar Creditos');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Créditos');
    }


    protected static function calculateCreditValues(
        float $valorCredito,
        float $porcentajeInteres,
        int $diasPlazo,
        ?string $formaPagoNombre,
        ?string $fechaCreditoStr,
        callable $set
    ): void {
        if ($valorCredito === null || $valorCredito <= 0 || $porcentajeInteres === null || $diasPlazo === null || $diasPlazo <= 0 || !$fechaCreditoStr) {
            $set('saldo_actual', null);
            $set('valor_cuota', null);
            $set('numero_cuotas', null);
            $set('fecha_vencimiento', null);
            $set('fecha_proximo_pago', null);
            return;
        }

        $valorTotal = $valorCredito * (1 + ($porcentajeInteres / 100));
        $set('saldo_actual', number_format($valorTotal, 2, '.', ''));

        $numeroCuotas = 0;
        switch (strtolower($formaPagoNombre)) {
            case 'diario':
                $numeroCuotas = $diasPlazo;
                break;
            case 'semanal':
                $numeroCuotas = ceil($diasPlazo / 7);
                break;
            case 'quincenal':
                $numeroCuotas = ceil($diasPlazo / 15);
                break;
            case 'mensual':
                $numeroCuotas = ceil($diasPlazo / 30);
                break;
            default:
                $numeroCuotas = $diasPlazo;
        }

        $numeroCuotas = max(1, $numeroCuotas);
        $set('numero_cuotas', (int) $numeroCuotas);

        $valorCuota = $valorTotal / $numeroCuotas;
        $set('valor_cuota', number_format($valorCuota, 2, '.', ''));

        $fechaCredito = Carbon::parse($fechaCreditoStr);
        $set('fecha_vencimiento', $fechaCredito->copy()->addDays($diasPlazo)->format('Y-m-d'));

        $nextPago = $fechaCredito->copy();
        switch (strtolower($formaPagoNombre)) {
            case 'diario':
                $nextPago->addDay();
                break;
            case 'semanal':
                $nextPago->addWeek();
                break;
            case 'quincenal':
                $nextPago->addDays(15);
                break;
            case 'mensual':
                $nextPago->addMonth();
                break;
            default:
                $nextPago->addDay();
        }
        $set('fecha_proximo_pago', $nextPago->format('Y-m-d'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    // Grid para dividir en dos columnas
                    Forms\Components\Grid::make(2)
                        ->schema([
                            // Columna izquierda (Datos de entrada)
                            Forms\Components\Group::make([
                                Forms\Components\Section::make('')
                                    ->schema([
                                        Select::make('id_cliente')
                                            ->label('Cliente *')
                                            ->options(function () {
                                                $clienteId = request()->query('cliente_id');

                                                if ($clienteId) {
                                                    $cliente = Clientes::find($clienteId);
                                                    if ($cliente) {
                                                        return [$cliente->id_cliente => $cliente->nombre_completo];
                                                    }
                                                }

                                                return Clientes::query()
                                                    ->where('activo', true)
                                                    ->get()
                                                    ->pluck('nombre_completo', 'id_cliente');
                                            })
                                            ->default(function () {
                                                $clienteId = request()->query('cliente_id');
                                                return Clientes::where('activo', true)->where('id_cliente', $clienteId)->exists() ? $clienteId : null;
                                            })
                                            ->required()
                                            ->searchable()
                                            ->columnSpanFull(),

                                        DatePicker::make('fecha_credito')
                                            ->label('Fecha del Crédito *')
                                            ->default(now())
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull()
                                            ->reactive()
                                            ->extraAttributes(['class' => 'h-[24px]'])
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $valorCredito = (float) $get('valor_credito');
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $dias = (int) $get('dias_plazo');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                static::calculateCreditValues($valorCredito, $porcentaje, $dias, $formaPagoNombre, $state, $set);
                                            }),

                                        TextInput::make('valor_credito')
                                            ->label('Valor del Crédito *')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->columnSpanFull()
                                            ->helperText('Por favor ingresa el valor de Crédito')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $dias = (int) $get('dias_plazo');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                $fechaCreditoStr = $get('fecha_credito');
                                                static::calculateCreditValues((float) $state, $porcentaje, $dias, $formaPagoNombre, $fechaCreditoStr, $set);
                                            }),

                                        TextInput::make('porcentaje_interes')
                                            ->label('Porcentaje *')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->columnSpanFull()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $valorCredito = (float) $get('valor_credito');
                                                $dias = (int) $get('dias_plazo');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                $fechaCreditoStr = $get('fecha_credito');
                                                static::calculateCreditValues($valorCredito, (float) $state, $dias, $formaPagoNombre, $fechaCreditoStr, $set);
                                            }),

                                        Select::make('forma_pago')
                                            ->label('Forma de Pago *')
                                            ->options(TipoPago::where('activo', true)->pluck('nombre', 'id_forma_pago'))
                                            ->required()
                                            ->searchable()
                                            ->columnSpanFull()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $valorCredito = (float) $get('valor_credito');
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $dias = (int) $get('dias_plazo');
                                                $formaPagoNombre = $state ? TipoPago::find($state)->nombre : null;
                                                $fechaCreditoStr = $get('fecha_credito');
                                                static::calculateCreditValues($valorCredito, $porcentaje, $dias, $formaPagoNombre, $fechaCreditoStr, $set);
                                            }),

                                        TextInput::make('dias_plazo')
                                            ->label('Días *')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->columnSpanFull()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $valorCredito = (float) $get('valor_credito');
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                $fechaCreditoStr = $get('fecha_credito');
                                                static::calculateCreditValues($valorCredito, $porcentaje, (int) $state, $formaPagoNombre, $fechaCreditoStr, $set);
                                            }),

                                        Select::make('orden_cobro')
                                            ->label('Orden de Cobro')
                                            ->options(OrdenCobro::where('activo', true)->pluck('nombre', 'id_orden_cobro'))
                                            ->default(2) // Asumiendo que 2 es "Último"
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ]),

                            // Columna derecha (Resultados calculados)
                            Forms\Components\Group::make([
                                Forms\Components\Section::make('')
                                    ->schema([
                                        TextInput::make('saldo_actual')
                                            ->label('Saldo')
                                            ->numeric()
                                            ->disabled()
                                            ->columnSpanFull(),

                                        TextInput::make('valor_cuota')
                                            ->label('Valor de la Cuota')
                                            ->numeric()
                                            ->disabled()
                                            ->columnSpanFull(),

                                        TextInput::make('numero_cuotas')
                                            ->label('No. de Cuotas')
                                            ->numeric()
                                            ->disabled()
                                            ->columnSpanFull(),

                                        DatePicker::make('fecha_vencimiento')
                                            ->label('Fecha de Vencimiento')
                                            ->disabled()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull()
                                            ->extraAttributes(['class' => 'h-[24px]']),

                                        DatePicker::make('fecha_proximo_pago')
                                            ->label('Fecha de Próximo Pago')
                                            ->disabled()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull()
                                            ->extraAttributes(['class' => 'h-[24px]']),

                                        Forms\Components\Repeater::make('conceptosCredito')
                                            ->label('Desglose del Crédito')
                                            ->relationship() // esto asume que tu modelo tiene ->conceptosCredito()
                                            ->schema([
                                                Select::make('tipo_concepto')
                                                    ->label('Tipo de Concepto')
                                                    ->options([
                                                        'Efectivo' => 'Efectivo',
                                                        'Yape' => 'Yape',
                                                        'Caja' => 'Caja',
                                                        'Saldo renovación' => 'Saldo renovación',
                                                        'Abono para completar préstamo' => 'Abono para completar préstamo',
                                                    ])
                                                    ->required()
                                                    ->reactive(), // Para mostrar/ocultar foto_comprobante según valor

                                                TextInput::make('monto')
                                                    ->label('Monto')
                                                    ->numeric()
                                                    ->required(),

                                               FileUpload::make('foto_comprobante')
                                                    ->label(fn ($get) => match ($get('tipo_concepto')) {
                                                        'Yape' => 'Comprobante Yape',
                                                        'Efectivo' => 'Comprobante Efectivo',
                                                        default => 'Comprobante'
                                                    })
                                                    ->directory(fn ($get) => match ($get('tipo_concepto')) {
                                                        'Yape' => 'comprobantes/yape',
                                                        'Efectivo' => 'comprobantes/efectivo',
                                                        default => 'comprobantes/generales'
                                                    })
                                                    ->visible(fn ($get) => in_array($get('tipo_concepto'), ['Yape', 'Efectivo']))
                                                    ->image()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                                    //->maxSize(2048) // 2MB máximo
                                                    ->columnSpanFull()
                                                    ->afterStateUpdated(function (TemporaryUploadedFile $state, $get) {
                                                        $directory = match ($get('tipo_concepto')) {
                                                            'Yape' => 'comprobantes/yape',
                                                            'Efectivo' => 'comprobantes/efectivo',
                                                            default => 'comprobantes/generales'
                                                        };

                                                        $image = Image::make($state->getRealPath())
                                                            ->resize(800, null, function ($constraint) {
                                                                $constraint->aspectRatio();
                                                            })
                                                            ->encode('jpg', 70); // 70% de calidad

                                                        Storage::disk('public')->put(
                                                            $directory . '/' . $state->getFilename(),
                                                            $image->stream()
                                                        );
                                                    }),
                                            ])
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->createItemButtonLabel('Agregar concepto')
                                            ->columns(2),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha_credito')
                    ->label('Fecha Crédito')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_credito')
                    ->label('Valor')
                    ->sortable(),

                Tables\Columns\TextColumn::make('porcentaje_interes')
                    ->label('Interés')
                    ->suffix('%')
                    ->sortable(),


                Tables\Columns\TextColumn::make('tipoPago.nombre')
                    ->label('Tipo')
                    ->sortable(),


                Tables\Columns\TextColumn::make('numero_cuotas')
                    ->label('Nr. cuotas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_cuota')
                    ->label('Cuota')
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo_actual')
                    ->label('Saldo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),


                Tables\Columns\TextColumn::make('conceptosCredito')
                    ->label('Detalle Entrega')
                    ->formatStateUsing(function ($record) {
                        return $record->conceptosCredito
                            ->map(fn($c) => "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2))
                            ->join(' | ');
                    })
                    ->wrap() // para que no se desborde si es muy largo
                    ->searchable(false),

                /*
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                ->label('último Pago')
                ->date('d/m/Y')
                ->sortable(),
                */

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn($record) => $record->saldo_actual > 0 ? 'Activo' : 'Pagado')
                    ->colors([
                        'success' => 'Activo',
                        'danger' => 'Pagado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('forma_pago')
                    ->label('Forma de Pago')
                    ->relationship('tipoPago', 'nombre'),

                Tables\Filters\SelectFilter::make('orden_cobro')
                    ->label('Orden de Cobro')
                    ->relationship('ordenCobro', 'nombre'),

                Tables\Filters\Filter::make('activos')
                    ->label('Solo créditos activos')
                    ->query(fn($query) => $query->where('saldo_actual', '>', 0)),
            ])
            ->actions([

                Tables\Actions\Action::make('view_abonos_history')
                    ->label('')
                    ->icon('heroicon-o-document-text') // Puedes usar 'heroicon-o-eye' o 'heroicon-o-document-text'
                    ->url(fn(Creditos $record): string => CreditosResource::getUrl('view', ['record' => $record->id_credito]))
                    ->color('primary')
                    ->tooltip('Ver Historial de Abonos')
                    ->button(), // Display as a button

                Tables\Actions\Action::make('view_comprobantes')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color(fn($record) => $record->conceptosCredito->where('foto_comprobante', '!=', null)->isNotEmpty() ? 'primary' : 'secondary')
                    ->size('sm')
                    ->button()
                    ->modalHeading('Comprobantes de Pago')
                    ->form(function ($record) {
                        $comprobantes = $record->conceptosCredito->where('foto_comprobante', '!=', null);

                        $components = [];

                        if ($comprobantes->isNotEmpty()) {
                            foreach ($comprobantes as $comprobante) {
                                $imageUrl = asset('storage/' . $comprobante->foto_comprobante);
                                $tipo = $comprobante->tipo_concepto;

                                $imageHtml = <<<HTML
                                    <div class="space-y-1 p-2">
                                        <p class="text-xs font-medium text-gray-500">Comprobante {$tipo}</p>
                                        <div class="flex justify-center">
                                            <img src="$imageUrl"
                                                class="rounded-lg max-h-[290px] max-w-full object-contain cursor-pointer"
                                                onclick="window.open(this.src, '_blank')">
                                        </div>
                                    </div>
                                HTML;

                                $components[] = \Filament\Forms\Components\Card::make()
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('comprobante_' . $comprobante->id)
                                            ->content(new \Illuminate\Support\HtmlString($imageHtml))
                                            ->disableLabel()
                                    ]);
                            }
                        } else {
                            $components[] = \Filament\Forms\Components\Placeholder::make('no_comprobantes')
                                ->content('No hay comprobantes disponibles')
                                ->disableLabel();
                        }

                        return $components;
                    })
                    ->modalWidth('xl')
                    ->modalButton('Cerrar')
                    ->hidden(fn($record) => $record->conceptosCredito->where('foto_comprobante', '!=', null)->isEmpty())
                    ->extraAttributes([
                        'title' => 'Ver Comprobantes',
                        'class' => 'hover:bg-success-50 rounded-full'
                    ])
                    ->action(function () {
                        // Acción vacía necesaria para el modal
                    })
            ])

            ->bulkActions([]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditos::route('/'),
            'create' => Pages\CreateCreditos::route('/create'),
            'edit' => Pages\EditCreditos::route('/{record}/edit'),
            'view' => Pages\ViewCredito::route('/{record}'), // Ensure this is active


        ];
    }
}
