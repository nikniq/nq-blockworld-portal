<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 32);
            $table->string('platform', 16);   // macos | linux | windows
            $table->string('arch', 16);       // arm64 | amd64
            $table->string('file_name');
            $table->string('file_path');      // relative to the local disk
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['version', 'platform', 'arch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
