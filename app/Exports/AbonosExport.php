<?php

namespace App\Exports;

use App\Models\Abonos;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Log;

class AbonosExport implements FromQuery, WithHeadings, WithMapping, WithDrawings, WithEvents, ShouldAutoSize
{
    protected $query;
    protected $rows = [];
    protected $allRecords;

    public function __construct($query)
    {
        $this->query = $query;
        $this->allRecords = $this->query
            ->with(['usuario', 'cliente', 'concepto', 'credito.tipoPago', 'conceptosabonos'])
            ->orderBy('fecha_pago', 'desc')
            ->get();

        Log::info("AbonosExport: registros cargados: " . $this->allRecords->count());
    }

    public function query()
    {
        return $this->query
            ->with(['usuario', 'cliente', 'concepto', 'credito.tipoPago', 'conceptosabonos'])
            ->orderBy('fecha_pago', 'desc');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Usuario',
            'Cliente',
            'Concepto',
            'Forma de Pago',
            'Cantidad',
            'Detalle Entrega',
            'Comprobante'
        ];
    }

    public function map($record): array
    {
        $this->rows[] = $record;

        return [
            $record->fecha_pago->format('d/m/Y H:i'),
            optional($record->usuario)->name ?? 'N/A',
            optional($record->cliente)->nombre ?? 'N/A',
            optional($record->concepto)->nombre ?? 'N/A',
            optional($record->credito->tipoPago)->nombre ?? 'N/A',
            'S/ ' . number_format($record->monto_abono, 2),
            $record->conceptosabonos
                ->map(fn($c) => "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2))
                ->join(' | '),
            '' // Columna vacía para que solo aparezca la imagen
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $startRow = 2; // Empieza después del encabezado

        // Usar $this->allRecords que ya tiene todos los datos cargados
        foreach ($this->allRecords as $index => $record) {
            $currentRow = $startRow + $index;

            foreach ($record->conceptosabonos as $concepto) {
                if ($concepto->foto_comprobante) {
                    $imagePath = storage_path('app/public/' . $concepto->foto_comprobante);

                    Log::info("Verificando imagen para fila {$currentRow}: {$imagePath}");
                    Log::info("¿Existe el archivo? " . (file_exists($imagePath) ? 'SÍ' : 'NO'));

                    if (file_exists($imagePath)) {
                        try {
                            $drawing = new Drawing();
                            $drawing->setName('Comprobante');
                            $drawing->setDescription('Comprobante de pago');
                            $drawing->setPath($imagePath);
                            $drawing->setHeight(100);
                            $drawing->setCoordinates("H{$currentRow}");
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawings[] = $drawing;

                            Log::info("✓ Imagen agregada en fila {$currentRow} para abono ID: {$record->id_abono}");
                        } catch (\Exception $e) {
                            Log::error("Error al agregar imagen en fila {$currentRow}: " . $e->getMessage());
                        }
                    } else {
                        Log::warning("✗ Imagen no encontrada para fila {$currentRow}: {$imagePath}");
                    }
                    break; // Solo una imagen por fila
                }
            }
        }

        Log::info("Total de imágenes agregadas: " . count($drawings));
        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($this->rows as $index => $record) {
                    $hasImage = collect($record->conceptosabonos)
                        ->contains(fn($c) => !empty($c->foto_comprobante));

                    $event->sheet->getRowDimension($index + 2)->setRowHeight($hasImage ? 110 : 20);
                }

                // Configuración de columnas
                $columns = ['A' => 15, 'B' => 20, 'C' => 25, 'D' => 20, 'E' => 15, 'F' => 12, 'G' => 30, 'H' => 20];
                foreach ($columns as $col => $width) {
                    $event->sheet->getColumnDimension($col)->setWidth($width);
                }

                // Estilo de encabezado
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Bordes para todas las celdas
                $event->sheet->getStyle('A1:H' . (count($this->rows) + 1))->applyFromArray([
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

                // Alinear cantidades a la derecha
                $event->sheet->getStyle('F2:F' . (count($this->rows) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
            },
        ];
    }
}