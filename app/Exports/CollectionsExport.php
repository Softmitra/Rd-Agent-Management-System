<?php

namespace App\Exports;

use App\Models\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollectionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $collections;

    public function __construct($collections)
    {
        $this->collections = $collections->load(['customer', 'rdAccount']);
    }

    public function collection()
    {
        return $this->collections;
    }

    public function headings(): array
    {
        return [
            'Receipt No',
            'Customer Name',
            'Account Number',
            'Amount',
            'Payment Date',
            'Payment Mode',
            'Status',
            'Remarks',
            'Collection Date'
        ];
    }

    public function map($collection): array
    {
        return [
            $collection->receipt_number,
            $collection->customer->name ?? 'N/A',
            $collection->rdAccount->account_number ?? 'N/A',
            '₹' . number_format($collection->amount, 2),
            $collection->payment_date ? $collection->payment_date->format('d/m/Y') : 'N/A',
            ucfirst($collection->payment_method),
            ucfirst($collection->status),
            $collection->remarks ?? '-',
            $collection->created_at ? $collection->created_at->format('d/m/Y H:i:s') : 'N/A'
        ];
    }

    // public function styles(Worksheet $sheet)
    // {
    //     return [
    //         // Style the header row
    //         1 => [
    //             'font' => ['bold' => true],
    //             'fill' => [
    //                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //                 'startColor' => ['rgb' => 'E0E0E0']
    //             ]
    //         ],

    //         // Style all cells with borders
    //         'A1:I' . ($this->collections->count() + 1) => [
    //             'borders' => [
    //                 'allBorders' => [
    //                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                 ],
    //             ],
    //         ],

    //         // Right align amount column
    //         'D1:D' . ($this->collections->count() + 1) => [
    //             'alignment' => [
    //                 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
    //             ],
    //         ],
    //     ];
    // }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            // Style the header row
            'A1:O1' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ],
            // Style all cells with borders
            'A1:O' . ($this->collections->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
            // Align currency columns to right
            'E1:G' . ($this->collections->count() + 1) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            // Align numeric columns to right
            'K1:K' . ($this->collections->count() + 1) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }
}
