<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Mobile Number',
            'Phone Number',
            'Date of Birth',
            'Address',
            'Aadhar Number',
            'PAN Number',
            'Agent',
            'Registration Date',
            'Has Savings Account',
            'CIF ID',
            'Savings Account No',
            'RD Accounts Count',
            'Status'
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->mobile_number,
            $customer->phone,
            $customer->date_of_birth->format('d/m/Y'),
            $customer->address,
            $customer->aadhar_number ?? 'N/A',
            $customer->pan_number ?? 'N/A',
            $customer->agent ? $customer->agent->name : 'N/A',
            $customer->created_at->format('d/m/Y'),
            $customer->has_savings_account ? 'Yes' : 'No',
            $customer->cif_id ?? 'N/A',
            $customer->savings_account_no ?? 'N/A',
            $customer->rdAccounts->count(),
            $customer->rdAccounts->count() > 0 ? 'Active' : 'Inactive'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
} 