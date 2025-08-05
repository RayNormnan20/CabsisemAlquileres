<?php

namespace App\Filament\Resources\AbonosResource\Pages;

use App\Filament\Resources\AbonosResource;
use App\Models\Clientes;
use App\Models\Concepto;
use App\Models\ConceptoAbono;
use App\Models\Creditos;
use App\Models\Ruta;
use App\Models\LogActividad;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;

class CreateAbonos extends CreateRecord
{
    protected static string $resource = AbonosResource::class;

    public ?int $currentRutaId = null;                                                                                                                                                                                                                                                   
    public $cliente_id;
    public $clientes;
    public $metodo_pago; // Nueva propiedad para el método de pago

    public function mount(): void
    {
        parent::mount();
            $this->currentRutaId = Session::get('selected_ruta_id');

        // Obtener lista de clientes con créditos activos
        $this->clientes = Clientes::whereHas('creditos', fn($q) => $q->where('saldo_actual', '>', 0))
            ->when($this->currentRutaId, function($query) {
                $query->where('id_ruta', $this->currentRutaId);
            })
            ->orderBy('nombre')
            ->get();

        // Obtener método de pago de la URL si existe
        $this->metodo_pago = request()->query('metodo_pago');

        // Precargar datos si viene con cliente_id
        $this->cliente_id = request()->query('cliente_id');
        if ($this->cliente_id) {
            $this->cargarDatosCliente($this->cliente_id);
        }
    }

    public function cargarDatosCliente($clienteId)
    {
        // Validar que el cliente pertenezca a la ruta actual
        $cliente = Clientes::where('id_cliente', $clienteId)
            ->when($this->currentRutaId, function($query) {
                $query->where('id_ruta', $this->currentRutaId);
            })
            ->first();

        if (!$cliente) {
            $this->notify('error', 'El cliente no existe o no pertenece a tu ruta actual');
            $this->cliente_id = null;
            $this->form->fill([]);
            return;
        }

        $this->cliente_id = $clienteId;
        $credito = Creditos::where('id_cliente', $clienteId)
            ->where('saldo_actual', '>', 0)
            ->first();
        
        // Obtener nombre Yape o nombre completo del cliente
        $yapeCliente = \App\Models\YapeCliente::where('id_cliente', $clienteId)->first();
        $nombreYape = $yapeCliente ? $yapeCliente->nombre : $cliente->nombre_completo;

        if ($credito) {
            $formData = [
                'id_cliente' => $clienteId,
                'id_credito' => $credito->id_credito,
                'cliente_nombre' => $cliente->nombre,
                'fecha_credito' => $credito->fecha_credito->format('d/m/Y'),
                'fecha_vencimiento' => $credito->fecha_vencimiento->format('d/m/Y'),
                'saldo_anterior' => $credito->saldo_actual,
                'monto_abono' => $credito->valor_cuota,
                'valor_cuota' => $credito->valor_cuota,
                'cuota' => $credito->cuota_diaria,
                'nombre_yape' => $nombreYape,
            ];

            // Si hay un método de pago específico, agregar datos iniciales
            if ($this->metodo_pago) {
                $formData['conceptosabonos'] = [
                    [
                        'tipo_concepto' => $this->metodo_pago,
                        'monto' => $credito->valor_cuota,
                        'referencia' => '',
                        'foto_comprobante' => null
                    ]
                ];
            }

            $this->form->fill($formData);
        } else {
            $this->notify('warning', 'El cliente no tiene créditos activos');
            $this->form->fill([
                'id_cliente' => $clienteId,
                'cliente_nombre' => $cliente->nombre,
                'nombre_yape' => $nombreYape,
            ]);
        }
    }
    protected function getHeader(): View
    {
        return view('filament.resources.abonos-resource.selector-cliente', [
            'clientes' => $this->clientes,
            'cliente_id' => $this->cliente_id,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['id_cliente'])) {
            throw new \Exception('No se especificó un cliente para el abono');
        }

        // Buscar el crédito activo del cliente
        $credito = Creditos::where('id_cliente', $data['id_cliente'])
            ->where('saldo_actual', '>', 0)
            ->first();

        if (!$credito) {
            throw new \Exception('El cliente no tiene créditos activos');
        }

       // $id_ruta = $this->obtenerIdRutaUsuario();
        $montoAbono = $data['monto_abono'] ?? 0;

        // Obtener el concepto "Abono" de la tabla conceptos
        $conceptoAbono = Concepto::where('nombre', 'Abono')->first();

        if (!$conceptoAbono) {
            throw new \Exception('El concepto "Abono" no existe en la tabla de conceptos');
        }

        // Asignar el id_concepto del abono
        $data['id_concepto'] = $conceptoAbono->id;

        // Calcular los saldos
        $data['id_credito'] = $credito->id_credito;
         $data['id_ruta'] = $this->currentRutaId;
        $data['saldo_anterior'] = $credito->saldo_actual;
        $data['saldo_posterior'] = $credito->saldo_actual - $montoAbono;
        $data['id_usuario'] = auth()->id();
        $data['fecha_pago'] = now();

        // Actualizar el crédito con el nuevo saldo y ruta
        $credito->saldo_actual = $data['saldo_posterior'];
        $credito->save();

        // Guardar/actualizar el nombre Yape si existe en los datos
        if (!empty($data['nombre_yape'])) {
            \App\Models\YapeCliente::updateOrCreate(
                ['id_cliente' => $data['id_cliente']],
                [
                    'nombre' => $data['nombre_yape'],
                    'user_id' => auth()->id(),
                    // Puedes agregar valores por defecto para otros campos si es necesario
                    'monto' => 0, // O el valor que corresponda
                    'entregar' => false // O el valor que corresponda
                ]
            );
        } else {
            // Opcional: Si no hay nombre Yape, puedes eliminar el registro existente
            // \App\Models\YapeCliente::where('id_cliente', $data['id_cliente'])->delete();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Confirmar que el crédito se actualizó correctamente
        $credito = Creditos::find($this->record->id_credito);
        if ($credito) {
            $credito->saldo_actual = $this->record->saldo_posterior;
            $credito->save();
        }

        // Obtener nombre completo del cliente y nombre de la ruta
        $clienteNombre = $this->record->cliente?->nombre . ' ' . $this->record->cliente?->apellido;
        $rutaNombre = $this->record->ruta?->nombre ?? 'Ruta desconocida';

        // Registrar actividad del abono con nombre del cliente y ruta
        LogActividad::registrar(
            'Abonos',
            "Se creó un abono de la ruta {$rutaNombre} para el cliente {$clienteNombre} por un monto de S/" . number_format($this->record->monto_abono, 2),
            [
                'id_abono' => $this->record->id,
                'id_cliente' => $this->record->id_cliente,
                'id_credito' => $this->record->id_credito,
                'monto_abono' => $this->record->monto_abono,
                'saldo_anterior' => $this->record->saldo_anterior,
                'saldo_posterior' => $this->record->saldo_posterior,
                'id_ruta' => $this->record->id_ruta,
            ]
        );
    }

   

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'cliente_id' => $this->record->id_cliente
        ]);
    }
}
