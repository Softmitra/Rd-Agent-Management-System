<?php

namespace App\Http\Controllers;

use App\Imports\RDAccountsImport;
use App\Models\Customer;
use App\Models\RDAccount;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ExcelImportController extends Controller
{
    /**
     * Show the Excel import page
     */
    public function index()
    {
        // Return appropriate view based on user role
        if (Auth::user() instanceof \App\Models\Agent) {
            return view('admin.excel-import.index');
        } else {
            return view('agent.excel-import.index');
        }
    }

    /**
     * Handle Excel file upload and preview
     */
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240' // 10MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $import = new RDAccountsImport();

            // Preview the data without importing
            $data = Excel::toArray($import, $file)[0];

            // Find the actual data start row by looking for meaningful data
            $dataStartRow = $this->findDataStartRow($data);

            // Extract only the data rows
            if ($dataStartRow !== -1) {
                $data = array_slice($data, $dataStartRow);
            }

            // Process and validate data
            $processedData = $this->processExcelData($data);

            // Store file temporarily for actual import
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'public');

            return response()->json([
                'success' => true,
                'data' => $processedData,
                'file_path' => $filePath,
                'total_records' => count($processedData['records']),
                'duplicates' => count($processedData['duplicates']),
                'errors' => count($processedData['errors'])
            ]);
        } catch (\Exception $e) {
            Log::error('Excel upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process and import the Excel data
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'agent_assignments' => 'array',
            'skip_duplicates' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $filePath = storage_path('app/public/' . $request->file_path);

            if (!file_exists($filePath)) {
                throw new \Exception('Import file not found');
            }

            $data = Excel::toArray(new RDAccountsImport(), $filePath)[0];

            // Find the actual data start row and extract data
            $dataStartRow = $this->findDataStartRow($data);

            if ($dataStartRow !== -1) {
                $data = array_slice($data, $dataStartRow);
            }

            $results = $this->importRecords($data, $request->agent_assignments ?? [], $request->skip_duplicates ?? false);

            // Clean up temporary file
            unlink($filePath);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Excel data and identify duplicates
     */
    private function processExcelData($data)
    {
        $records = [];
        $duplicates = [];
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                // Map Excel columns to our fields - handle different column positions
                // Try to find account name and amount in different positions
                $accountName = '';
                $rdAmount = 0;
                $accountNumber = '';
                $totalDeposit = 0;
                $installments = 1;

                // Based on actual Excel data structure: [null,null,null,"C298506303",null,"020002473414","SHILABAI NAMDEV ROKADE","500.00 Cr.",...]
                // Column 5: account number, Column 6: customer name, Column 7: amount with "Cr." suffix

                // Extract account number from column 5
                if (!empty($row[5])) {
                    $accountNumber = trim($row[5]);
                }

                // Extract customer name from column 6
                if (!empty($row[6]) && !is_numeric($row[6])) {
                    $accountName = trim($row[6]);
                }

                // Extract amount from column 7 - parse "500.00 Cr." format
                if (!empty($row[7])) {
                    $amountText = trim($row[7]);
                    if (preg_match('/([0-9,]+\.?[0-9]*) *(Cr|Dr)\.?/i', $amountText, $matches)) {
                        $amountStr = str_replace(',', '', $matches[1]);
                        $rdAmount = (float)$amountStr;
                    } else {
                        // Log amount format mismatch
                        Log::warning('Amount format mismatch in Excel import', [
                            'row' => $index + 2,
                            'amount_text' => $amountText,
                            'expected_format' => 'numeric with Cr/Dr suffix',
                            'column_index' => 7
                        ]);
                    }
                }

                // If we found multiple numeric values, use logic to determine which is amount
                $numericValues = [];
                foreach ($row as $cellValue) {
                    if (is_numeric($cellValue) && (float)$cellValue > 0) {
                        $numericValues[] = (float)$cellValue;
                    }
                }

                // Parse amounts from text (e.g., "500.00 Cr.", "1,400.00 Cr.")
                foreach ($row as $cellValue) {
                    if (is_string($cellValue) && preg_match('/([0-9,]+\.?[0-9]*) *(Cr|Dr)\.?/i', trim($cellValue), $matches)) {
                        $amountStr = str_replace(',', '', $matches[1]);
                        $amount = (float)$amountStr;
                        if ($amount >= 50 && $amount <= 50000) { // Reasonable RD amount range
                            $rdAmount = $amount;
                            break;
                        }
                    }
                }

                // If no amount found from text parsing, try numeric values
                if ($rdAmount == 0 && count($numericValues) > 0) {
                    foreach ($numericValues as $val) {
                        if ($val >= 50 && $val <= 50000) { // Reasonable RD amount
                            $rdAmount = $val;
                            break;
                        }
                    }
                    // If still no reasonable amount, use smallest numeric value (likely the amount)
                    if ($rdAmount == 0) {
                        $rdAmount = min($numericValues);
                    }
                }

                $record = [
                    'row_number' => $index + 2,
                    'banking_ref_no' => $row[0] ?? '',
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'rd_denomination' => $rdAmount,
                    'total_deposit' => $totalDeposit,
                    'installments' => $installments,
                    'status' => 'Success'
                ];

                // Validate only essential fields
                if (empty($record['account_name']) || $record['rd_denomination'] <= 0) {
                    $errors[] = [
                        'row' => $record['row_number'],
                        'error' => 'Missing required data (Name: "' . $record['account_name'] . '" or Amount: ' . $record['rd_denomination'] . ')',
                        'data' => $record
                    ];
                    continue;
                }

                // Check for duplicate customers
                $existingCustomer = Customer::where('name', $record['account_name'])->first();

                if ($existingCustomer) {
                    // Check if same customer already has an RD account with the same amount
                    $existingRdAccount = RDAccount::where('customer_id', $existingCustomer->id)
                        ->where('monthly_amount', $record['rd_denomination'])
                        ->first();

                    if ($existingRdAccount) {
                        // True duplicate - same customer, same RD amount (cannot import)
                        $duplicates[] = [
                            'row' => $record['row_number'],
                            'customer_name' => $record['account_name'],
                            'existing_customer_id' => $existingCustomer->id,
                            'existing_agent' => $existingCustomer->agent->name ?? 'Unknown',
                            'duplicate_type' => 'exact_duplicate', // Same customer + same amount
                            'existing_rd_amount' => $existingRdAccount->monthly_amount,
                            'existing_account_number' => $existingRdAccount->account_number,
                            'remark' => 'Exact duplicate found - same customer with same RD amount (₹' . number_format($existingRdAccount->monthly_amount, 2) . ')',
                            'can_import' => false,
                            'data' => $record
                        ];
                    } else {
                        // Allowed duplicate - same customer, different RD amount (can import)
                        $duplicates[] = [
                            'row' => $record['row_number'],
                            'customer_name' => $record['account_name'],
                            'existing_customer_id' => $existingCustomer->id,
                            'existing_agent' => $existingCustomer->agent->name ?? 'Unknown',
                            'duplicate_type' => 'customer_exists', // Same customer + different amount
                            'new_rd_amount' => $record['rd_denomination'],
                            'remark' => 'Customer exists - will create new RD account with amount ₹' . number_format($record['rd_denomination'], 2),
                            'can_import' => true,
                            'data' => $record
                        ];
                    }
                } else {
                    $records[] = $record;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 2,
                    'error' => 'Processing error: ' . $e->getMessage(),
                    'data' => $row
                ];
            }
        }

        return [
            'records' => $records,
            'duplicates' => $duplicates,
            'errors' => $errors
        ];
    }

    /**
     * Import records into database
     */
    private function importRecords($data, $agentAssignments = [], $skipDuplicates = false)
    {
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Get default agent (current logged-in agent or first active agent)
        $defaultAgent = null;
        $currentUser = Auth::user();

        // Debug logging
        Log::info('Excel Import - Current User:', [
            'user_id' => $currentUser ? $currentUser->id : null,
            'user_name' => $currentUser ? $currentUser->name : null,
            'user_email' => $currentUser ? $currentUser->email : null,
            'is_agent_model' => $currentUser instanceof \App\Models\Agent,
            'auth_guard' => Auth::getDefaultDriver()
        ]);

        // Always use current authenticated user if it's an Agent model
        if ($currentUser instanceof \App\Models\Agent) {
            $defaultAgent = $currentUser;
        } else {
            // If not an agent (maybe admin), use first active agent
            $defaultAgent = Agent::where('is_active', true)->first();
        }

        // Additional logging to ensure we have the right agent
        Log::info('Final default agent selected:', [
            'default_agent_id' => $defaultAgent ? $defaultAgent->id : 'none',
            'default_agent_name' => $defaultAgent ? $defaultAgent->name : 'none'
        ]);

        foreach ($data as $index => $row) {
            try {
                // Use same smart detection logic as in processExcelData
                $accountName = '';
                $rdDenomination = 0;
                $accountNumber = '';
                $totalDeposit = 0;
                $installments = 1;

                // Based on actual Excel data structure: [null,null,null,"C298506303",null,"020002473414","SHILABAI NAMDEV ROKADE","500.00 Cr.",...]
                // Column 5: account number, Column 6: customer name, Column 7: amount with "Cr." suffix

                // Extract account number from column 5
                if (!empty($row[5])) {
                    $accountNumber = trim($row[5]);
                }

                // Extract customer name from column 6
                if (!empty($row[6]) && !is_numeric($row[6])) {
                    $accountName = trim($row[6]);
                }

                // Extract amount from column 7 - parse "500.00 Cr." format
                if (!empty($row[7])) {
                    $amountText = trim($row[7]);
                    if (preg_match('/([0-9,]+\.?[0-9]*) *(Cr|Dr)\.?/i', $amountText, $matches)) {
                        $amountStr = str_replace(',', '', $matches[1]);
                        $rdDenomination = (float)$amountStr;
                    } else {
                        // Log amount format mismatch
                        Log::warning('Amount format mismatch in Excel import (importRecords)', [
                            'row' => $index + 2,
                            'amount_text' => $amountText,
                            'expected_format' => 'numeric with Cr/Dr suffix',
                            'column_index' => 7
                        ]);
                    }
                }

                // Find numeric values for amounts
                $numericValues = [];
                foreach ($row as $cellValue) {
                    if (is_numeric($cellValue) && (float)$cellValue > 0) {
                        $numericValues[] = (float)$cellValue;
                    }
                }

                // Parse amounts from text (e.g., "500.00 Cr.", "1,400.00 Cr.")
                foreach ($row as $cellValue) {
                    if (is_string($cellValue) && preg_match('/([0-9,]+\.?[0-9]*) *(Cr|Dr)\.?/i', trim($cellValue), $matches)) {
                        $amountStr = str_replace(',', '', $matches[1]);
                        $amount = (float)$amountStr;
                        if ($amount >= 50 && $amount <= 50000) { // Reasonable RD amount range
                            $rdDenomination = $amount;
                            break;
                        }
                    }
                }

                // If no amount found from text parsing, try numeric values
                if ($rdDenomination == 0 && count($numericValues) > 0) {
                    foreach ($numericValues as $val) {
                        if ($val >= 50 && $val <= 50000) { // Reasonable RD amount
                            $rdDenomination = $val;
                            break;
                        }
                    }
                    // If still no reasonable amount, use smallest numeric value (likely the amount)
                    if ($rdDenomination == 0) {
                        $rdDenomination = min($numericValues);
                    }
                }

                if (empty($accountName) || $rdDenomination <= 0) {
                    // Log missing essential fields mismatch
                    Log::warning('Missing essential fields in Excel import', [
                        'row' => $index + 2,
                        'account_name' => $accountName,
                        'rd_denomination' => $rdDenomination,
                        'missing_fields' => empty($accountName) ? 'account_name' : 'rd_denomination'
                    ]);
                    $errors++;
                    continue;
                }

                // Check for existing customer
                $existingCustomer = Customer::where('name', $accountName)->first();

                if ($existingCustomer) {
                    // Check if same customer already has an RD account with the same amount
                    $existingRdAccount = RDAccount::where('customer_id', $existingCustomer->id)
                        ->where('monthly_amount', $rdDenomination)
                        ->first();

                    if ($existingRdAccount) {
                        // True duplicate - same customer, same RD amount (skip this)
                        Log::warning('Exact duplicate found - skipping import', [
                            'row' => $index + 2,
                            'customer_name' => $accountName,
                            'existing_customer_id' => $existingCustomer->id,
                            'existing_rd_account_id' => $existingRdAccount->id,
                            'existing_rd_amount' => $existingRdAccount->monthly_amount,
                            'new_rd_amount' => $rdDenomination
                        ]);
                        $skipped++;
                        continue;
                    }
                    // If customer exists but with different RD amount, continue with import
                } else if ($existingCustomer && $skipDuplicates) {
                    $skipped++;
                    continue;
                }

                // Determine agent assignment - Always use current logged-in agent for new imports
                $agentId = $agentAssignments[$index] ?? $defaultAgent->id;

                // Log agent assignment for debugging
                Log::info('Agent assignment for customer:', [
                    'customer_name' => $accountName,
                    'assigned_agent_id' => $agentId,
                    'default_agent_id' => $defaultAgent ? $defaultAgent->id : 'none',
                    'current_user_id' => Auth::id(),
                    'existing_customer_agent' => $existingCustomer ? $existingCustomer->agent_id : 'new customer'
                ]);

                // Create or update customer
                if ($existingCustomer) {
                    $customer = $existingCustomer;
                } else {
                    // Generate unique placeholder phone numbers to avoid constraint violations
                    $placeholderPhone = $this->generatePlaceholderPhone($customer ?? null);

                    $customer = Customer::create([
                        'name' => $accountName,
                        'mobile_number' => $placeholderPhone,
                        'phone' => $placeholderPhone,
                        'agent_id' => $agentId,
                        'has_savings_account' => false,
                        'data_source' => 'excel_import',
                        'is_complete' => false
                    ]);
                }

                // Use the identified account number or generate one
                if (empty($accountNumber)) {
                    $accountNumber = $this->generateAccountNumber();
                }

                // Calculate proper maturity amount with compound interest
                $maturityAmount = $this->calculateMaturityAmount($rdDenomination, 60, 6.5);

                // Create RD Account
                $rdAccount = RDAccount::create([
                    'customer_id' => $customer->id,
                    'agent_id' => $agentId,
                    'account_number' => $accountNumber,
                    'account_type' => 'RD',
                    'monthly_amount' => $rdDenomination,
                    'total_deposited' => $totalDeposit,
                    'duration_months' => 60, // Default 5 years
                    'installments_paid' => $installments,
                    'interest_rate' => 6.5, // Default rate
                    'start_date' => Carbon::now(),
                    'maturity_date' => Carbon::now()->addMonths(60),
                    'maturity_amount' => $maturityAmount,
                    'status' => 'active',
                    'registered_phone' => $placeholderPhone,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id()
                ]);

                $imported++;
            } catch (\Exception $e) {
                Log::error('Import record error: ' . $e->getMessage(), [
                    'row' => $index + 2,
                    'data' => $row
                ]);
                $errors++;
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => count($data)
        ];
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber()
    {
        do {
            $accountNumber = 'RD' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (RDAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Find the row where actual data starts by looking for meaningful content
     */
    private function findDataStartRow($data)
    {
        for ($i = 0; $i < count($data); $i++) {
            $row = $data[$i];

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Look for typical header indicators that we should skip
            $rowText = strtolower(implode(' ', array_filter($row)));

            // Skip rows that contain report headers, titles, or criteria
            $skipPatterns = [
                'recurring deposit',
                'installment report',
                'search criteria',
                'report generated',
                'date range',
                'branch',
                'total records',
                'summary'
            ];

            $shouldSkip = false;
            foreach ($skipPatterns as $pattern) {
                if (strpos($rowText, $pattern) !== false) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            // Look for a row that has the expected data pattern
            // Updated column structure based on actual data: [null,null,null,"C298506303",null,"020002473414","SHILABAI NAMDEV ROKADE","500.00 Cr.",null,null,"500.00 Cr.",null,"1","0.00","0.00"]
            // Column 5: account number, Column 6: customer name, Column 7: amount
            if (count($row) >= 8) {
                $accountName = trim($row[6] ?? ''); // Customer name is in column 6
                $rdAmount = $row[7] ?? '';         // Amount is in column 7

                // Check if this looks like actual data:
                // - Column C (account name) should have meaningful text (not just numbers or codes)
                // - Column D (RD amount) should be numeric
                if (
                    !empty($accountName) &&
                    strlen($accountName) > 3 &&
                    !is_numeric($accountName) &&
                    (is_numeric($rdAmount) || preg_match('/[0-9,\.]+/', $rdAmount))
                ) {

                    // Additional check: skip if it looks like a column header
                    $headerIndicators = ['name', 'account name', 'customer', 'amount', 'denomination'];
                    $isHeader = false;
                    foreach ($headerIndicators as $indicator) {
                        if (stripos($accountName, $indicator) !== false) {
                            $isHeader = true;
                            break;
                        }
                    }

                    if (!$isHeader) {
                        return $i; // Found the data start row
                    }
                }
            }
        }

        return -1; // No data found
    }

    /**
     * Generate unique placeholder phone number to avoid constraint violations
     */
    private function generatePlaceholderPhone($customer = null)
    {
        // Generate a unique placeholder phone number
        // Format: starts with 999 followed by timestamp-based unique number
        $baseNumber = '999' . substr(str_replace('.', '', microtime(true)), -7);

        // Ensure it's exactly 10 digits and doesn't conflict
        while (
            Customer::where('mobile_number', $baseNumber)->exists() ||
            Customer::where('phone', $baseNumber)->exists()
        ) {
            $baseNumber = '999' . substr(str_replace('.', '', microtime(true)), -7);
            usleep(1000); // Small delay to ensure different timestamps
        }

        return $baseNumber;
    }

    /**
     * Calculate maturity amount with compound interest
     */
    private function calculateMaturityAmount($monthlyAmount, $durationMonths, $interestRate)
    {
        // RD Maturity calculation with compound interest
        // Formula: A = P * n * (n+1) / 2 * (12 + r) / 12
        // Where: P = monthly deposit, n = number of months, r = annual interest rate

        $P = $monthlyAmount;
        $n = $durationMonths;
        $r = $interestRate / 100;

        // Simple maturity calculation for RD
        $totalDeposits = $P * $n;
        $maturityAmount = $P * $n * (($n + 1) / 2) * ((12 + $r) / 12) / $n;

        return round($maturityAmount, 2);
    }

    /**
     * Get agents list for assignment
     */
    public function getAgents()
    {
        $agents = Agent::where('is_active', true)
            ->select('id', 'name')
            ->get();

        return response()->json($agents);
    }
}
