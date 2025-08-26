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
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_reference_number')->unique(); // From India Post
            $table->date('lot_date');
            $table->string('lot_description')->nullable();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->integer('total_accounts')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(3.75);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->enum('status', ['draft', 'processing', 'completed', 'verified'])->default('draft');
            $table->string('import_file_name')->nullable(); // Original Excel file name
            $table->text('import_errors')->nullable(); // JSON of import errors
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();

            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['agent_id', 'lot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
