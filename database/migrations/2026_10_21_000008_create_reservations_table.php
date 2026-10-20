<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('screening_id')->constrained()->restrictOnDelete();
            $table->string('reference', 16)->unique();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->string('status', 20)->default('confirmed')->index();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('fees')->default(0);
            $table->unsignedInteger('total');
            $table->char('currency', 3);
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason', 240)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['screening_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
