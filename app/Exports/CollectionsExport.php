<?php

namespace App\Exports;

use App\Models\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collections;

    public function __construct($collections)
    {
        $this->collections = $collections;
    }

    public function collection()
    {
        return $this->collections;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Account Number',
            'Date',
            'Amount',
            'Payment Type',
            'Status',
            'Note'
        ];
    }

    public function map($collection): array
    {
        return [
            $collection->id,
            $collection->customer->name,
            $collection->rdAccount->account_number,
            $collection->date,
            $collection->amount,
            ucfirst($collection->payment_type),
            ucfirst($collection->status),
            $collection->note
        ];
    }
}
