<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('handle', 32)->unique()->after('name');
            $table->string('bio', 500)->nullable()->after('password');
            $table->string('platform', 16)->nullable()->after('bio');
            $table->string('favourite_mode', 16)->nullable()->after('platform');
            $table->unsignedInteger('best_score')->default(0)->after('favourite_mode');
            $table->string('avatar_path')->nullable()->after('best_score');
            $table->boolean('is_admin')->default(false)->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['handle', 'bio', 'platform', 'favourite_mode', 'best_score', 'avatar_path', 'is_admin']);
        });
    }
};
