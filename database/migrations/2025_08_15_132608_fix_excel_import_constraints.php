<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix customers table constraints
        Schema::table('customers', function (Blueprint $table) {
            // Drop unique constraints temporarily
            $table->dropUnique(['mobile_number']);
            $table->dropUnique(['phone']);
        });
        
        Schema::table('customers', function (Blueprint $table) {
            // Make mobile_number and phone nullable and not unique during import
            $table->string('mobile_number', 15)->nullable()->change();
            $table->string('phone', 15)->nullable()->change();
        });
        
        // Fix rd_accounts table constraints
        Schema::table('rd_accounts', function (Blueprint $table) {
            // Increase monthly_amount precision to handle larger numbers temporarily
            $table->decimal('monthly_amount', 15, 2)->change();
            $table->decimal('maturity_amount', 20, 2)->change();
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
        
        Schema::table('rd_accounts', function (Blueprint $table) {
            $table->decimal('monthly_amount', 10, 2)->change();
            $table->decimal('maturity_amount', 12, 2)->change();
        });
    }
};
