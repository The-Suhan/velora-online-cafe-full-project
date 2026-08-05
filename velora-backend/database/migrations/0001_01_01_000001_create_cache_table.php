<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CACHE_STORE=database'in ihtiyaç duyduğu tablolar.
 *
 * Laravel'in varsayılan cache migration'ı projeden düşmüş; users migration'ında
 * sessions/personal_access_tokens elle toplanmış ama cache atlanmış. Sonuç:
 * AuthController::register bekleyen kaydı Cache'e yazarken 42P01 ile çöküyordu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
