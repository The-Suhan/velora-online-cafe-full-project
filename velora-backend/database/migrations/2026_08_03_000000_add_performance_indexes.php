<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the indexes the admin/client list endpoints actually filter and sort on.
 *
 * `foreignId()->constrained()` only creates the foreign key, not an index on the
 * referencing column, so every join/filter below was a sequential scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // OrderController::index — status filter + "newest first" ordering
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            // stats/chart/dashboard — date-range scans across every status
            $table->index('created_at', 'orders_created_at_index');
            // CustomerController::myOrders — own orders, newest first
            $table->index(['user_id', 'created_at'], 'orders_user_id_created_at_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'order_items_order_id_index');
            $table->index('product_id', 'order_items_product_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            // CustomerController::products / categoryProducts — active products per category
            $table->index(['category_id', 'is_active'], 'products_category_id_is_active_index');
            // sort=rating
            $table->index('avg_rating', 'products_avg_rating_index');
            // default "newest" ordering
            $table->index('created_at', 'products_created_at_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            // CustomerController::categories — active roots + active children
            $table->index(['parent_id', 'is_active'], 'categories_parent_id_is_active_index');
        });

        Schema::table('ratings', function (Blueprint $table) {
            // avg_rating recalculation + productRatings listing.
            // The existing unique(user_id, product_id) only covers user_id as a prefix.
            $table->index('product_id', 'ratings_product_id_index');
        });

        Schema::table('feedbacks', function (Blueprint $table) {
            // FeedbackController::pending / resolved
            $table->index(['is_done', 'created_at'], 'feedbacks_is_done_created_at_index');
            $table->index('user_id', 'feedbacks_user_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            // AdminController::users — role filter, newest first
            $table->index(['role', 'created_at'], 'users_role_created_at_index');
        });

        Schema::table('otp_codes', function (Blueprint $table) {
            // AuthController looks up the latest unused code for a user + type
            $table->index(['user_id', 'type', 'is_used'], 'otp_codes_user_id_type_is_used_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_user_id_created_at_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_id_index');
            $table->dropIndex('order_items_product_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_id_is_active_index');
            $table->dropIndex('products_avg_rating_index');
            $table->dropIndex('products_created_at_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_id_is_active_index');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex('ratings_product_id_index');
        });

        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropIndex('feedbacks_is_done_created_at_index');
            $table->dropIndex('feedbacks_user_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_created_at_index');
        });

        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropIndex('otp_codes_user_id_type_is_used_index');
        });
    }
};
