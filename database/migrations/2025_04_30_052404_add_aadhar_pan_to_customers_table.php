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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('aadhar_number')->nullable()->unique();
            $table->string('pan_number')->nullable()->unique();
            $table->string('aadhar_file')->nullable();
            $table->string('pan_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'aadhar_number',
                'pan_number',
                'aadhar_file',
                'pan_file'
            ]);
        });
    }
};
