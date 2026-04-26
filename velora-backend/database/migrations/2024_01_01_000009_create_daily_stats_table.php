<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('stat_date')->unique()->nullable(false);
            $table->integer('order_count')->default(0);
            $table->integer('pending_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->integer('active_users')->default(0);
            $table->decimal('avg_order_value', 10, 2)->default(0.00);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
    }
};