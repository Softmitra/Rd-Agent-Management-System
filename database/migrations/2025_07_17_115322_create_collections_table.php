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
Schema::create('collections', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->enum('payment_type', ['cash', 'upi', 'cheque', 'online']);
    $table->text('note')->nullable();
    $table->foreignId('agent_id')->constrained('agents');
    $table->foreignId('customer_id')->constrained('customers');
    $table->decimal('amount', 10, 2);
    $table->enum('status', ['submitted', 'pending', 'settled'])->default('submitted');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
