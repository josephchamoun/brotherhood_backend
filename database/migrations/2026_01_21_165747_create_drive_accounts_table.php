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
        Schema::create('drive_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('title');
            $table->text('password'); // 🔐 encrypted, but viewable after decrypt
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_accounts');
    }
};
