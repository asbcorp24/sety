<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('practice_key', 100)->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('errors_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_attempts');
    }
};
