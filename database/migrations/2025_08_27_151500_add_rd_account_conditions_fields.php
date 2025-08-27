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
            // Payment method (cash/cheque)
            if (!Schema::hasColumn('rd_accounts', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'cheque'])->default('cash')->after('monthly_amount');
            }

            // Cheque details (if payment method is cheque)
            if (!Schema::hasColumn('rd_accounts', 'cheque_number')) {
                $table->string('cheque_number')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('rd_accounts', 'cheque_date')) {
                $table->date('cheque_date')->nullable()->after('cheque_number');
            }
            if (!Schema::hasColumn('rd_accounts', 'cheque_bank')) {
                $table->string('cheque_bank')->nullable()->after('cheque_date');
            }

            // Nomination facility
            if (!Schema::hasColumn('rd_accounts', 'nominee_name')) {
                $table->string('nominee_name')->nullable()->after('joint_holder_name');
            }
            if (!Schema::hasColumn('rd_accounts', 'nominee_relation')) {
                $table->string('nominee_relation')->nullable()->after('nominee_name');
            }
            if (!Schema::hasColumn('rd_accounts', 'nominee_phone')) {
                $table->string('nominee_phone', 10)->nullable()->after('nominee_relation');
            }

            // Additional joint holders (up to 3 adults total including primary)
            if (!Schema::hasColumn('rd_accounts', 'joint_holder_2_name')) {
                $table->string('joint_holder_2_name')->nullable()->after('joint_holder_name');
            }
            if (!Schema::hasColumn('rd_accounts', 'joint_holder_3_name')) {
                $table->string('joint_holder_3_name')->nullable()->after('joint_holder_2_name');
            }

            // Transfer between post offices
            if (!Schema::hasColumn('rd_accounts', 'previous_post_office')) {
                $table->string('previous_post_office')->nullable()->after('nominee_phone');
            }
            if (!Schema::hasColumn('rd_accounts', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('previous_post_office');
            }

            // Premature closure
            if (!Schema::hasColumn('rd_accounts', 'premature_closure_date')) {
                $table->date('premature_closure_date')->nullable()->after('maturity_date');
            }
            if (!Schema::hasColumn('rd_accounts', 'premature_closure_amount')) {
                $table->decimal('premature_closure_amount', 12, 2)->nullable()->after('premature_closure_date');
            }

            // Loan facility
            if (!Schema::hasColumn('rd_accounts', 'loan_availed')) {
                $table->boolean('loan_availed')->default(false)->after('premature_closure_amount');
            }
            if (!Schema::hasColumn('rd_accounts', 'loan_amount')) {
                $table->decimal('loan_amount', 12, 2)->nullable()->after('loan_availed');
            }
            if (!Schema::hasColumn('rd_accounts', 'loan_date')) {
                $table->date('loan_date')->nullable()->after('loan_amount');
            }
            if (!Schema::hasColumn('rd_accounts', 'loan_repayment_date')) {
                $table->date('loan_repayment_date')->nullable()->after('loan_date');
            }

            // Interest compounding frequency
            if (!Schema::hasColumn('rd_accounts', 'interest_compounding')) {
                $table->enum('interest_compounding', ['monthly', 'quarterly', 'yearly'])->default('quarterly')->after('interest_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rd_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'cheque_number',
                'cheque_date',
                'cheque_bank',
                'nominee_name',
                'nominee_relation',
                'nominee_phone',
                'joint_holder_2_name',
                'joint_holder_3_name',
                'previous_post_office',
                'transfer_date',
                'premature_closure_date',
                'premature_closure_amount',
                'loan_availed',
                'loan_amount',
                'loan_date',
                'loan_repayment_date',
                'interest_compounding'
            ]);
        });
    }
};
