<?php

namespace App\Exports;

use App\Models\Creditos;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;

class CreditosExcelExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected $query;
    protected $rows = [];
    protected $allRecords;

    public function __construct($query)
    {
        $this->query = $query;
        $this->allRecords = $this->query
            ->with(['cliente', 'tipoPago', 'conceptosCredito', 'abonos'])
            ->orderBy('fecha_credito', 'desc')
            ->get();

        Log::info("CreditosExcelExport: registros cargados: " . $this->allRecords->count());
    }

    public function query()
    {
        return $this->query
            ->with(['cliente', 'tipoPago', 'conceptosCredito', 'abonos'])
            ->orderBy('fecha_credito', 'desc');
    }

    public function headings(): array
    {
        return [
            'Fecha Crédito',
            'Cliente',
            'Valor Crédito',
            'Interés (%)',
            'Forma de Pago',
            'Días Plazo',
            'Número Cuotas',
            'Valor Cuota',
            'Saldo Actual',
            'Estado',
            'Fecha Vencimiento',
            'Fecha Próximo Pago',
            'Total Abonos',
            'Cantidad Abonos'
        ];
    }

    public function map($credito): array
    {
        $row = [
            $credito->fecha_credito ? $credito->fecha_credito->format('d/m/Y') : '',
            $credito->cliente ? $credito->cliente->nombre_completo : 'Sin cliente',
            number_format($credito->valor_credito, 2, '.', ','),
            $credito->es_adicional ? '0' : $credito->porcentaje_interes,
            $credito->tipoPago ? $credito->tipoPago->nombre : 'Sin especificar',
            $credito->dias_plazo,
            $credito->numero_cuotas,
            number_format($credito->valor_cuota, 2, '.', ','),
            number_format($credito->saldo_actual, 2, '.', ','),
            $credito->saldo_actual > 0 ? 'Activo' : 'Cancelado',
            $credito->fecha_vencimiento ? $credito->fecha_vencimiento->format('d/m/Y') : '',
            $credito->fecha_proximo_pago ? $credito->fecha_proximo_pago->format('d/m/Y') : '',
            number_format($credito->abonos->sum('monto_abono'), 2, '.', ','),
            $credito->abonos->count()
        ];

        $this->rows[] = $row;
        return $row;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Estilo para el encabezado
                $event->sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Bordes para todas las celdas
                $event->sheet->getStyle('A1:N' . (count($this->rows) + 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Alinear valores monetarios a la derecha
                $event->sheet->getStyle('C2:C' . (count($this->rows) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
                $event->sheet->getStyle('H2:I' . (count($this->rows) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
                $event->sheet->getStyle('M2:M' . (count($this->rows) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                // Alinear números a la derecha
                $event->sheet->getStyle('F2:G' . (count($this->rows) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
                $event->sheet->getStyle('N2:N' . (count($this->rows) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
            },
        ];
    }
}