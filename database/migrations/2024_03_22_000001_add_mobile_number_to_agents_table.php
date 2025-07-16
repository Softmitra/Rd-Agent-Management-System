<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Agent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the column without unique constraint
        Schema::table('agents', function (Blueprint $table) {
            $table->string('mobile_number')->nullable()->after('email');
        });

        // Update existing records with a default value
        $agents = Agent::whereNull('mobile_number')->get();
        foreach ($agents as $agent) {
            $agent->update([
                'mobile_number' => '0000000000' . $agent->id
            ]);
        }

        // Now add the unique constraint
        Schema::table('agents', function (Blueprint $table) {
            $table->unique('mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropUnique(['mobile_number']);
            $table->dropColumn('mobile_number');
        });
    }
}; 