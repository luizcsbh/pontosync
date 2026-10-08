<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_day_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['entry', 'lunch_start', 'lunch_end', 'exit']);
            $table->dateTime('recorded_at');
            $table->enum('source', ['manual', 'ocr', 'correction'])->default('manual');
            $table->decimal('ocr_confidence', 3, 2)->nullable();
            $table->string('ocr_raw_text')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->dateTime('original_recorded_at')->nullable()->comment('Valor original antes de edição (auditoria)');
            $table->timestamps();

            $table->unique(['work_day_id', 'type'], 'unique_type_per_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_records');
    }
};
