<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_schedule_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->comment('0=Domingo, 1=Segunda, ..., 6=Sábado');
            $table->boolean('is_workday')->default(true);
            $table->unsignedSmallInteger('entry_time_minutes')->nullable()->comment('Minutos desde 00:00');
            $table->unsignedSmallInteger('lunch_start_minutes')->nullable();
            $table->unsignedSmallInteger('lunch_end_minutes')->nullable();
            $table->unsignedSmallInteger('exit_time_minutes')->nullable();
            $table->unsignedSmallInteger('expected_minutes')->nullable()->comment('Total de minutos esperados de trabalho');
            $table->timestamps();

            $table->unique(['work_schedule_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedule_days');
    }
};
