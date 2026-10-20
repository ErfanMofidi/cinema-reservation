<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->string('tagline', 240)->nullable();
            $table->text('synopsis');
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->unsignedSmallInteger('runtime_minutes');
            $table->string('age_rating', 12);
            $table->string('language', 40);
            $table->date('release_date')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
