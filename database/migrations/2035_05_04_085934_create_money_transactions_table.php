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
        Schema::create('money_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('moneybox_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2); // positive or negative

            $table->enum('type', ['income', 'expense']);

            $table->string('source')->nullable(); 
            // examples: event, manual, donation, purchase

            $table->text('description')->nullable();

            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_transactions');
    }
};
