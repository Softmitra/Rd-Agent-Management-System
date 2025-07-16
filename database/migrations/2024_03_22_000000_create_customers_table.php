<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('mobile_number', 10)->unique();
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('cif_id')->nullable()->unique();
            $table->string('savings_account_no')->nullable()->unique();
            $table->boolean('has_savings_account')->default(false);
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->json('additional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
}; 