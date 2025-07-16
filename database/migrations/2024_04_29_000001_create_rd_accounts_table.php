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
        Schema::create('rd_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->string('account_type')->default('RD');
            $table->boolean('is_joint_account')->default(false);
            $table->string('joint_holder_name')->nullable();
            $table->string('account_number')->unique();
            $table->decimal('monthly_amount', 10, 2);
            $table->decimal('total_deposited', 10, 2)->default(0);
            $table->integer('duration_months');
            $table->decimal('maturity_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->date('start_date');
            $table->date('maturity_date');
            $table->enum('status', ['active', 'matured', 'closed'])->default('active');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->string('registered_phone', 10);
            $table->enum('half_month_period', ['first', 'second'])->nullable();
            $table->integer('installments_paid')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rd_accounts');
    }
}; 