<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('section_user_roles', function (Blueprint $table) {
        // Step 1: add columns as nullable
        $table->date('start_date')->nullable()->after('role_id');
        $table->date('end_date')->nullable()->after('start_date');
    });

    // Step 2: backfill existing records
    DB::table('section_user_roles')
        ->whereNull('start_date')
        ->update(['start_date' => now()->toDateString()]);

    Schema::table('section_user_roles', function (Blueprint $table) {
        // Step 3: enforce NOT NULL after data exists
        $table->date('start_date')->nullable(false)->change();

        // Fix unique constraints
        $table->dropUnique(['user_id', 'section_id']);

        $table->unique(
            ['user_id', 'section_id', 'role_id', 'start_date'],
            'section_user_role_unique_period'
        );
    });
}


   public function down(): void
{
    Schema::table('section_user_roles', function (Blueprint $table) {
        $table->dropUnique('section_user_role_unique_period');
        $table->unique(['user_id', 'section_id']);

        $table->dropColumn(['start_date', 'end_date']);
    });
}

};
