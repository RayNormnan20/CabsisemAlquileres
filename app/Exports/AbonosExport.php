<?php

namespace App\Exports;

use App\Models\Abonos;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbonosExport implements FromQuery, WithHeadings, WithMapping, WithDrawings, WithEvents, ShouldAutoSize
{
    protected $query;
    protected $rows = [];

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with([
            'usuario',
            'cliente',
            'concepto',
            'credito.tipoPago',
            'conceptosabonos'
        ]);
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
            $record->usuario->name,
            $record->cliente->nombre,
            optional($record->concepto)->nombre ?? '',
            optional($record->credito->tipoPago)->nombre ?? '',
            number_format($record->monto_abono, 2),
            $record->conceptosabonos
                ->map(fn($c) => "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2))
                ->join(' | '),
            $record->conceptosabonos->firstWhere('foto_comprobante', '!=', null) ? 'Ver imagen' : ''
        ];
    }

    public function drawings()
{
    $drawings = [];
    $row = 2; // Empieza después del encabezado

    foreach ($this->rows as $record) {
        foreach ($record->conceptosabonos as $concepto) {
            if ($concepto->foto_comprobante) {
                // Construye la ruta correcta según el tipo
                $tipo = strtolower($concepto->tipo_concepto);
                $path = storage_path("app/public/storage/comprobantes/abonos/{$tipo}/" . basename($concepto->foto_comprobante));

                if (file_exists($path)) {
                    $drawing = new Drawing();
                    $drawing->setName('Comprobante');
                    $drawing->setDescription('Comprobante de pago');
                    $drawing->setPath($path);
                    $drawing->setHeight(80);
                    $drawing->setWidth(120);
                    $drawing->setCoordinates("H{$row}");
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                    break; // Solo una imagen por fila
                }
            }
        }
        $row++;
    }

    return $drawings;
}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Ajustar altura de filas con imágenes
                foreach ($this->rows as $index => $record) {
                    if ($record->conceptosabonos->firstWhere('foto_comprobante', '!=', null)) {
                        $event->sheet->getRowDimension($index + 2)->setRowHeight(80);
                    }
                }

                // Estilos para el encabezado
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD'],
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
                ]);
            },
        ];
    }
}