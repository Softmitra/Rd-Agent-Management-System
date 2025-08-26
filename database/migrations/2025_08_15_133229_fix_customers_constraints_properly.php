<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to remove unique constraints properly
        try {
            DB::statement('ALTER TABLE customers DROP INDEX customers_mobile_number_unique');
        } catch (Exception $e) {
            // Constraint might not exist or already dropped
        }
        
        try {
            DB::statement('ALTER TABLE customers DROP INDEX customers_phone_unique');
        } catch (Exception $e) {
            // Constraint might not exist or already dropped  
        }
        
        // Make mobile_number and phone nullable
        Schema::table('customers', function (Blueprint $table) {
            $table->string('mobile_number', 15)->nullable()->change();
            $table->string('phone', 15)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('mobile_number', 10)->unique()->change();
            $table->string('phone')->unique()->change();
        });
    }
};
