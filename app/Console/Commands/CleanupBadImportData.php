<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\RDAccount;

class CleanupBadImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:bad-import-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up problematic data from failed Excel imports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of problematic import data...');
        
        // Clean up RD accounts with unreasonable monthly amounts (over 100,000)
        $problematicRD = RDAccount::where('monthly_amount', '>', 100000)->get();
        $this->info("Found {$problematicRD->count()} RD accounts with unreasonable monthly amounts.");
        
        foreach ($problematicRD as $rd) {
            $this->line("Deleting RD Account: {$rd->account_number} with amount: {$rd->monthly_amount}");
            $rd->delete();
        }
        
        // Clean up customers with duplicate placeholder phone numbers
        $duplicatePhones = Customer::where('mobile_number', '0000000000')->get();
        $this->info("Found {$duplicatePhones->count()} customers with duplicate placeholder phone numbers.");
        
        foreach ($duplicatePhones as $customer) {
            $this->line("Deleting customer: {$customer->name} (ID: {$customer->id})");
            $customer->delete();
        }
        
        $this->info('Cleanup completed successfully!');
        return 0;
    }
}
