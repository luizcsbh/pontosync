<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hour_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_day_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('description');
            $table->smallInteger('minutes')->comment('Positivo = crédito, Negativo = débito');
            $table->smallInteger('balance_minutes')->comment('Saldo acumulado após esta transação');
            $table->enum('type', ['daily', 'manual_adjustment', 'correction'])->default('daily');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hour_bank_transactions');
    }
};
