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
            // Add missing fields
            if (!Schema::hasColumn('rd_accounts', 'account_type')) {
                $table->string('account_type')->default('RD')->after('agent_id');
            }
            
            if (!Schema::hasColumn('rd_accounts', 'is_joint_account')) {
                $table->boolean('is_joint_account')->default(false)->after('account_type');
            }
            
            if (!Schema::hasColumn('rd_accounts', 'joint_holder_name')) {
                $table->string('joint_holder_name')->nullable()->after('is_joint_account');
            }
            
            if (!Schema::hasColumn('rd_accounts', 'total_deposited')) {
                $table->decimal('total_deposited', 10, 2)->default(0)->after('monthly_amount');
            }
            
            if (!Schema::hasColumn('rd_accounts', 'half_month_period')) {
                $table->enum('half_month_period', ['first', 'second'])->nullable()->after('registered_phone');
            }
            
            // Rename paid_months to installments_paid if it exists
            if (Schema::hasColumn('rd_accounts', 'paid_months')) {
                $table->renameColumn('paid_months', 'installments_paid');
            } else if (!Schema::hasColumn('rd_accounts', 'installments_paid')) {
                $table->integer('installments_paid')->default(0)->after('half_month_period');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rd_accounts', function (Blueprint $table) {
            // Drop the added columns in reverse order
            $table->dropColumn([
                'account_type',
                'is_joint_account',
                'joint_holder_name',
                'total_deposited',
                'half_month_period',
                'installments_paid'
            ]);
        });
    }
}; 