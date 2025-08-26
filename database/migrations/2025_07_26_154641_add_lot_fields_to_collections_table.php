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
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignId('lot_id')->nullable()->constrained('lots')->onDelete('set null');
            $table->enum('lot_status', ['not_in_lot', 'assigned_to_lot', 'processed'])->default('not_in_lot');
            $table->foreignId('rd_account_id')->nullable()->constrained('rd_accounts')->onDelete('set null');
            $table->integer('months_paid')->nullable(); // Number of months this collection covers
            
            $table->index(['lot_status']);
            $table->index(['agent_id', 'lot_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->dropForeign(['rd_account_id']);
            $table->dropColumn(['lot_id', 'lot_status', 'rd_account_id', 'months_paid']);
        });
    }
};
