<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Live order tracking.
 *
 * The polling endpoints page through orders by `updated_at`, which none of the
 * existing indexes cover (add_performance_indexes only indexes created_at).
 *
 * order_status_history records every status transition so the customer-facing
 * tracker can show *when* each step happened, not just where the order is now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // OrderUpdatesController::customer — own orders changed since a cursor
            $table->index(['user_id', 'updated_at'], 'orders_user_id_updated_at_index');
            // OrderUpdatesController::admin — any order changed since a cursor
            $table->index('updated_at', 'orders_updated_at_index');
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            // OrderController::destroy hard-deletes orders, so history goes with them.
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status')->nullable(); // null on the initial "pending"
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['order_id', 'created_at'], 'order_status_history_order_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_updated_at_index');
            $table->dropIndex('orders_updated_at_index');
        });
    }
};
