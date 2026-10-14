<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 64)->unique();
            $table->string('title', 80);
            $table->text('description')->nullable();
            $table->string('mode', 16)->default('survival');   // survival | creative | zombie | battle
            $table->string('world_size', 16)->default('large'); // normal | large | huge
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('nights')->default(0);
            $table->string('save_path');
            $table->unsignedBigInteger('save_size')->default(0);
            $table->string('screenshot_path')->nullable();
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamps();
            $table->index(['mode', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_games');
    }
};
