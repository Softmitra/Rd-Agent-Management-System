<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\RDAccount;
use App\Models\Agent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RDAccountsImport implements ToModel, WithValidation, SkipsOnError, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // This is used for direct import, but we'll handle it in controller
        // for more control over the process
        return null;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '2' => 'required|string|max:255', // Account Name
            '3' => 'required|numeric|min:0.01', // RD Denomination
            '4' => 'nullable|numeric|min:0', // Total Deposit
            '5' => 'nullable|integer|min:0', // Installments
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '2.required' => 'Account name is required',
            '2.max' => 'Account name cannot exceed 255 characters',
            '3.required' => 'RD denomination is required',
            '3.numeric' => 'RD denomination must be a number',
            '3.min' => 'RD denomination must be greater than 0',
            '4.numeric' => 'Total deposit must be a number',
            '5.integer' => 'Installments must be a whole number',
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
