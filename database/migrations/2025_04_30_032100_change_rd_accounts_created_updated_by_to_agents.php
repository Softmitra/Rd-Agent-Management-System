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
        Schema::table('rd_accounts', function (Blueprint $table) {
            // First drop the existing foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Then modify the columns to reference agents table
            $table->foreignId('created_by')->change()->constrained('agents')->onDelete('cascade');
            $table->foreignId('updated_by')->change()->constrained('agents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rd_accounts', function (Blueprint $table) {
            // Drop the agent foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Restore the user foreign keys
            $table->foreignId('created_by')->change()->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->change()->constrained('users')->onDelete('cascade');
        });
    }
}; 