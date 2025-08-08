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

    public function cargarDatosCliente($clienteId = null)
    {
        // Si no se pasa clienteId como parámetro, intentar obtenerlo de los datos del formulario
        if (!$clienteId) {
            $clienteId = $this->data['id_cliente'] ?? null;
        }

        if (!$clienteId) {
            return;
        }

        $cliente = Clientes::find($clienteId);

        if (!$cliente) {
            return;
        }

        $this->cliente_id = $clienteId;
        $credito = Creditos::where('id_cliente', $clienteId)
            ->where('saldo_actual', '>', 0)
            ->first();

        // SIEMPRE usar el nombre completo del cliente como valor por defecto
        // NO buscar automáticamente un YapeCliente relacionado
        $nombreYape = $cliente->nombre_completo;

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
                'id_yape_cliente' => null, // Asegurar que no hay YapeCliente seleccionado por defecto
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
                'id_yape_cliente' => null, // Asegurar que no hay YapeCliente seleccionado por defecto
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

        // SOLO guardar el id_yape_cliente si existe, sin crear/actualizar YapeCliente
        // El id_yape_cliente ya viene en $data desde el formulario
        // No hacemos nada más con el nombre_yape

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
