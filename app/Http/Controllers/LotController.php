<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Collection;
use App\Models\RdAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class LotController extends Controller
{
    /**
     * Display a listing of lots.
     */
    public function index(Request $request)
    {
        $query = Lot::with(['agent', 'collections']);

        // Filter by agent if user is an agent (not admin with id = 1)
        if (auth()->user()->id != 1) {
            $query->where('agent_id', Auth::id());
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('lot_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('lot_date', '<=', $request->date_to);
        }

        if ($request->filled('agent_id') && auth()->user()->id == 1) {
            $query->where('agent_id', $request->agent_id);
        }

        $lots = $query->orderBy('lot_date', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate(15);

        return view('lots.index', compact('lots'));
    }

    /**
     * Show the form for creating a new lot.
     */
    public function create()
    {
        return view('lots.create');
    }

    /**
     * Store a newly created lot.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lot_reference_number' => 'required|string|unique:lots',
            'lot_date' => 'required|date',
            'lot_description' => 'nullable|string|max:255',
            'commission_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $lot = Lot::create([
            'lot_reference_number' => $request->lot_reference_number,
            'lot_date' => $request->lot_date,
            'lot_description' => $request->lot_description,
            'agent_id' => Auth::id(),
            'commission_percentage' => $request->commission_percentage ?? 3.75,
            'status' => 'draft'
        ]);

        return redirect()->route('lots.show', $lot)
                        ->with('success', 'Lot created successfully.');
    }

    /**
     * Display the specified lot.
     */
    public function show(Lot $lot)
    {
        // Ensure agent can only view their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        $lot->load(['collections.rdAccount.customer', 'agent']);
        
        // Get available collections for this agent that are not in any lot
        $availableCollections = Collection::with(['rdAccount.customer'])
            ->where('agent_id', $lot->agent_id)
            ->where('lot_status', 'not_in_lot')
            ->orderBy('date', 'desc')
            ->get();

        return view('lots.show', compact('lot', 'availableCollections'));
    }

    /**
     * Show the form for editing the specified lot.
     */
    public function edit(Lot $lot)
    {
        // Ensure agent can only edit their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if (!$lot->canBeModified()) {
            return redirect()->route('lots.show', $lot)
                           ->with('error', 'This lot cannot be modified in its current status.');
        }

        return view('lots.edit', compact('lot'));
    }

    /**
     * Update the specified lot.
     */
    public function update(Request $request, Lot $lot)
    {
        // Ensure agent can only update their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if (!$lot->canBeModified()) {
            return redirect()->route('lots.show', $lot)
                           ->with('error', 'This lot cannot be modified in its current status.');
        }

        $request->validate([
            'lot_reference_number' => 'required|string|unique:lots,lot_reference_number,' . $lot->id,
            'lot_date' => 'required|date',
            'lot_description' => 'nullable|string|max:255',
            'commission_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $lot->update([
            'lot_reference_number' => $request->lot_reference_number,
            'lot_date' => $request->lot_date,
            'lot_description' => $request->lot_description,
            'commission_percentage' => $request->commission_percentage ?? 3.75,
        ]);

        $lot->calculateCommission();

        return redirect()->route('lots.show', $lot)
                        ->with('success', 'Lot updated successfully.');
    }

    /**
     * Remove the specified lot.
     */
    public function destroy(Lot $lot)
    {
        // Ensure agent can only delete their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if (!$lot->canBeModified()) {
            return redirect()->route('lots.index')
                           ->with('error', 'This lot cannot be deleted in its current status.');
        }

        // Remove collections from lot
        $lot->collections()->update([
            'lot_id' => null,
            'lot_status' => 'not_in_lot'
        ]);

        $lot->delete();

        return redirect()->route('lots.index')
                        ->with('success', 'Lot deleted successfully.');
    }

    /**
     * Show import form.
     */
    public function showImport()
    {
        return view('lots.import');
    }

    /**
     * Import lot from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'lot_reference_number' => 'required|string|unique:lots',
            'lot_date' => 'required|date',
            'lot_description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Create the lot first
            $lot = Lot::create([
                'lot_reference_number' => $request->lot_reference_number,
                'lot_date' => $request->lot_date,
                'lot_description' => $request->lot_description,
                'agent_id' => Auth::id(),
                'commission_percentage' => 3.75,
                'status' => 'processing',
                'import_file_name' => $request->file('excel_file')->getClientOriginalName()
            ]);

            // Store the uploaded file temporarily
            $filePath = $request->file('excel_file')->store('temp');
            $fullPath = Storage::path($filePath);

            // Process the Excel file
            $result = $this->processExcelFile($fullPath, $lot);

            // Clean up temporary file
            Storage::delete($filePath);

            if ($result['success']) {
                $lot->update([
                    'status' => 'completed',
                    'import_errors' => $result['errors']
                ]);
                
                $lot->updateTotals();
                
                DB::commit();

                $message = "Lot imported successfully. {$result['matched']} accounts matched";
                if (count($result['errors']) > 0) {
                    $errorCount = count($result['errors']);
                    $message .= ", {$errorCount} errors found.";
                }

                return redirect()->route('lots.show', $lot)
                               ->with('success', $message);
            } else {
                DB::rollBack();
                return redirect()->back()
                               ->with('error', 'Import failed: ' . $result['message'])
                               ->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lot import failed: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Import failed: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Process Excel file and match with collections.
     */
    private function processExcelFile($filePath, Lot $lot)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            $matched = 0;
            $errors = [];

            // Assuming Excel format: Account No (A), Amount (B), Date (C)
            for ($row = 2; $row <= $highestRow; $row++) {
                $accountNumber = $worksheet->getCell('A' . $row)->getValue();
                $amount = (float) $worksheet->getCell('B' . $row)->getValue();
                $dateValue = $worksheet->getCell('C' . $row)->getValue();

                // Skip empty rows
                if (empty($accountNumber) && empty($amount)) {
                    continue;
                }

                try {
                    // Find RD Account
                    $rdAccount = RdAccount::where('account_number', $accountNumber)
                                         ->where('agent_id', $lot->agent_id)
                                         ->first();

                    if (!$rdAccount) {
                        $errors[] = [
                            'row' => $row,
                            'account_number' => $accountNumber,
                            'amount' => $amount,
                            'error' => 'RD Account not found in system'
                        ];
                        continue;
                    }

                    // Find matching collection
                    $collection = Collection::where('rd_account_id', $rdAccount->id)
                                          ->where('amount', $amount)
                                          ->where('lot_status', 'not_in_lot')
                                          ->where('agent_id', $lot->agent_id)
                                          ->first();

                    if (!$collection) {
                        $errors[] = [
                            'row' => $row,
                            'account_number' => $accountNumber,
                            'amount' => $amount,
                            'error' => 'No matching collection entry found (check amount and lot status)'
                        ];
                        continue;
                    }

                    // Assign collection to lot
                    $collection->assignToLot($lot);
                    $matched++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $row,
                        'account_number' => $accountNumber,
                        'amount' => $amount,
                        'error' => 'Processing error: ' . $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'matched' => $matched,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => []
            ];
        }
    }

    /**
     * Manually assign a collection to lot.
     */
    public function assignCollection(Request $request, Lot $lot)
    {
        // Ensure agent can only modify their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if (!$lot->canBeModified()) {
            return response()->json(['error' => 'Lot cannot be modified'], 400);
        }

        $request->validate([
            'collection_id' => 'required|exists:collections,id'
        ]);

        $collection = Collection::find($request->collection_id);

        if ($collection->agent_id !== $lot->agent_id) {
            return response()->json(['error' => 'Collection does not belong to this agent'], 400);
        }

        if ($collection->assignToLot($lot)) {
            $lot->updateTotals();
            return response()->json(['success' => 'Collection assigned to lot successfully']);
        }

        return response()->json(['error' => 'Failed to assign collection to lot'], 400);
    }

    /**
     * Remove a collection from lot.
     */
    public function removeCollection(Request $request, Lot $lot)
    {
        // Ensure agent can only modify their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if (!$lot->canBeModified()) {
            return response()->json(['error' => 'Lot cannot be modified'], 400);
        }

        $request->validate([
            'collection_id' => 'required|exists:collections,id'
        ]);

        $collection = Collection::find($request->collection_id);

        if ($collection->lot_id !== $lot->id) {
            return response()->json(['error' => 'Collection is not assigned to this lot'], 400);
        }

        if ($collection->removeFromLot()) {
            $lot->updateTotals();
            return response()->json(['success' => 'Collection removed from lot successfully']);
        }

        return response()->json(['error' => 'Failed to remove collection from lot'], 400);
    }

    /**
     * Download lot import errors.
     */
    public function downloadErrors(Lot $lot)
    {
        // Ensure agent can only access their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if (empty($lot->import_errors)) {
            return redirect()->back()->with('error', 'No errors found for this lot.');
        }

        $errors = $lot->import_errors;
        $content = "Row,Account Number,Amount,Error\n";
        
        foreach ($errors as $error) {
            $content .= "{$error['row']},{$error['account_number']},{$error['amount']},\"{$error['error']}\"\n";
        }

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="lot_' . $lot->lot_reference_number . '_errors.csv"');
    }

    /**
     * Finalize/Complete a lot.
     */
    public function finalize(Lot $lot)
    {
        // Ensure agent can only finalize their own lots
        if (auth()->user()->id != 1 && $lot->agent_id !== Auth::id()) {
            abort(403);
        }

        if ($lot->status !== 'draft' && $lot->status !== 'processing') {
            return redirect()->back()->with('error', 'Lot cannot be finalized in its current status.');
        }

        if ($lot->collections()->count() === 0) {
            return redirect()->back()->with('error', 'Cannot finalize an empty lot.');
        }

        $lot->update(['status' => 'completed']);
        $lot->updateTotals();

        return redirect()->route('lots.show', $lot)
                        ->with('success', 'Lot finalized successfully.');
    }

    /**
     * Verify a lot (Admin only).
     */
    public function verify(Lot $lot)
    {
        // Only admins can verify lots
        if (auth()->user()->id != 1) {
            abort(403);
        }

        if ($lot->status !== 'completed') {
            return redirect()->back()->with('error', 'Only completed lots can be verified.');
        }

        $lot->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        return redirect()->route('admin.lots.show', $lot)
                        ->with('success', 'Lot verified successfully.');
    }
}
