<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->enum('status', ['incomplete', 'complete', 'absent', 'holiday', 'off'])->default('incomplete');
            $table->unsignedSmallInteger('expected_minutes')->default(480)->comment('Minutos esperados de trabalho (padrão 8h = 480min)');
            $table->smallInteger('worked_minutes')->nullable()->comment('Minutos efetivamente trabalhados');
            $table->smallInteger('balance_minutes')->nullable()->comment('Saldo: worked_minutes - expected_minutes');
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_days');
    }
};
