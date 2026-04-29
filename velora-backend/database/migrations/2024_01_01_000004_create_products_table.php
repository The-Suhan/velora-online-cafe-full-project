<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable(false);   
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->timestamps(); // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
