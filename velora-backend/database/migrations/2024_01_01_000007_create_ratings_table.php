<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('score', 2, 1)->nullable(false); // 0.5 - 5.0 arası
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Bir kullanıcı aynı ürünü sadece bir kez değerlendirebilir
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
