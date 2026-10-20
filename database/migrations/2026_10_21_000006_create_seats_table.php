<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditorium_id')->constrained('auditoriums')->cascadeOnDelete();
            $table->string('row_label', 3);
            $table->unsignedSmallInteger('seat_number');
            $table->string('seat_type', 20)->default('standard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['auditorium_id', 'row_label', 'seat_number']);
            $table->index(['auditorium_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
