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
            $table->boolean('is_complete')->default(false)->after('status');
            $table->timestamp('completed_at')->nullable()->after('is_complete');
            $table->text('completion_notes')->nullable()->after('completed_at');
            $table->enum('data_source', ['manual', 'excel_import'])->default('manual')->after('completion_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rd_accounts', function (Blueprint $table) {
            $table->dropColumn(['is_complete', 'completed_at', 'completion_notes', 'data_source']);
        });
    }
};
