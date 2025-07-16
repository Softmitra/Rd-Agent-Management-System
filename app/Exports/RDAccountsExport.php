<?php

namespace App\Exports;

use App\Models\RDAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RDAccountsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $rdAccounts;

    public function __construct($rdAccounts)
    {
        $this->rdAccounts = $rdAccounts->load(['customer', 'agent']);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->rdAccounts;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Account Number',
            'Customer Name',
            'Customer Phone',
            'Agent Name',
            'Monthly Amount',
            'Total Deposited',
            'Maturity Amount',
            'Opening Date',
            'Maturity Date',
            'Duration (Months)',
            'Interest Rate',
            'Installments Paid',
            'Half Month Period',
            'Status',
            'Created At'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->account_number,
            $row->customer->name,
            $row->registered_phone,
            $row->agent->name,
            '₹' . number_format($row->monthly_amount, 2),
            '₹' . number_format($row->total_deposited, 2),
            '₹' . number_format($row->maturity_amount, 2),
            $row->start_date->format('d/m/Y'),
            $row->maturity_date->format('d/m/Y'),
            $row->duration_months,
            $row->interest_rate . '%',
            $row->installments_paid,
            ucfirst($row->half_month_period) . ' Half',
            ucfirst($row->status),
            $row->created_at->format('d/m/Y H:i:s')
        ];
    }

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
            'A1:O' . ($this->rdAccounts->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],

            // Align currency columns to right
            'E1:G' . ($this->rdAccounts->count() + 1) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],

            // Align numeric columns to right
            'K1:K' . ($this->rdAccounts->count() + 1) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }
} 