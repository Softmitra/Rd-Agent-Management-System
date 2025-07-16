<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Agent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the columns without unique constraints
        Schema::table('agents', function (Blueprint $table) {
            $table->string('agent_id')->after('id')->nullable();
            $table->string('aadhar_number')->after('email')->nullable();
            $table->string('pan_number')->after('aadhar_number')->nullable();
            $table->string('aadhar_file')->nullable()->after('pan_number');
            $table->string('pan_file')->nullable()->after('aadhar_file');
            $table->boolean('is_verified')->default(false)->after('branch');
            $table->boolean('is_active')->default(true)->after('is_verified');
            $table->timestamp('account_expires_at')->nullable()->after('is_active');
            $table->timestamp('verified_at')->nullable()->after('email_verified_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                  ->constrained('agents')->nullOnDelete();
            $table->text('verification_remarks')->nullable()->after('verified_by');
        });

        // Generate agent IDs for existing records
        foreach (Agent::whereNull('agent_id')->get() as $agent) {
            $agent->update([
                'agent_id' => 'AG' . strtoupper(Str::random(8))
            ]);
        }

        // Now add the unique constraints
        Schema::table('agents', function (Blueprint $table) {
            $table->unique('agent_id');
            $table->unique('aadhar_number');
            $table->unique('pan_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropUnique(['agent_id']);
            $table->dropUnique(['aadhar_number']);
            $table->dropUnique(['pan_number']);
            $table->dropColumn([
                'agent_id',
                'aadhar_number',
                'pan_number',
                'aadhar_file',
                'pan_file',
                'is_verified',
                'is_active',
                'account_expires_at',
                'verified_at',
                'verified_by',
                'verification_remarks'
            ]);
        });
    }
};
