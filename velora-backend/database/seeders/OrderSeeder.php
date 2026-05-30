<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $orders = [
            [
                'user_id' => 1,
                'status' => 'delivered',
                'delivery_type' => 'pickup',
                'address' => null,
                'phone' => '+99361000001',
                'note' => 'No onions please.',
                'total_price' => 27.90,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
                'items' => [
                    ['product_name' => 'Margherita',       'quantity' => 1, 'price' => 8.50,  'subtotal' => 8.50],
                    ['product_name' => 'Caesar Salad',     'quantity' => 1, 'price' => 7.50,  'subtotal' => 7.50],
                    ['product_name' => 'French Fries',     'quantity' => 2, 'price' => 3.50,  'subtotal' => 7.00],
                    ['product_name' => 'Cappuccino',       'quantity' => 1, 'price' => 3.50,  'subtotal' => 3.50],
                    ['product_name' => 'Garlic Sauce',     'quantity' => 1, 'price' => 1.00,  'subtotal' => 1.00],
                    ['product_name' => 'Ketchup',          'quantity' => 1, 'price' => 0.50,  'subtotal' => 0.50],
                ],
            ],
            [
                'user_id' => 1,
                'status' => 'delivered',
                'delivery_type' => 'delivery',
                'address' => '123 Main Street, Apt 4B',
                'phone' => '+99361234567',
                'note' => null,
                'total_price' => 42.30,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
                'items' => [
                    ['product_name' => 'Mixed Grill',          'quantity' => 1, 'price' => 14.90, 'subtotal' => 14.90],
                    ['product_name' => 'Adana Kebab',          'quantity' => 1, 'price' => 11.00, 'subtotal' => 11.00],
                    ['product_name' => 'Greek Salad',          'quantity' => 1, 'price' => 6.90,  'subtotal' => 6.90],
                    ['product_name' => 'Lemonade',             'quantity' => 2, 'price' => 2.90,  'subtotal' => 5.80],
                    ['product_name' => 'BBQ Sauce',            'quantity' => 1, 'price' => 1.00,  'subtotal' => 1.00],
                    ['product_name' => 'Garlic Sauce',         'quantity' => 1, 'price' => 1.00,  'subtotal' => 1.00],
                    ['product_name' => 'Ketchup',              'quantity' => 1, 'price' => 0.50,  'subtotal' => 0.50],
                    ['product_name' => 'Cheese Sauce',         'quantity' => 1, 'price' => 1.20,  'subtotal' => 1.20],
                ],
            ],
            [
                'user_id' => 1,
                'status' => 'preparing',
                'delivery_type' => 'pickup',
                'address' => null,
                'phone' => '+99361000001',
                'note' => 'Extra spicy.',
                'total_price' => 19.80,
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2),
                'items' => [
                    ['product_name' => 'Pepperoni',            'quantity' => 1, 'price' => 9.50,  'subtotal' => 9.50],
                    ['product_name' => 'Cola',                 'quantity' => 2, 'price' => 2.50,  'subtotal' => 5.00],
                    ['product_name' => 'Tiramisu',             'quantity' => 1, 'price' => 5.90,  'subtotal' => 5.90],
                ],
            ],
            [
                'user_id' => 1,
                'status' => 'cancelled',
                'delivery_type' => 'delivery',
                'address' => '45 Park Avenue',
                'phone' => '+99365559988',
                'note' => null,
                'total_price' => 15.40,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
                'items' => [
                    ['product_name' => 'Chicken Burger',       'quantity' => 1, 'price' => 7.50,  'subtotal' => 7.50],
                    ['product_name' => 'Onion Rings',          'quantity' => 2, 'price' => 3.90,  'subtotal' => 7.80],
                ],
            ],
            [
                'user_id' => 1,
                'status' => 'pending',
                'delivery_type' => 'pickup',
                'address' => null,
                'phone' => '+99361000001',
                'note' => null,
                'total_price' => 22.50,
                'created_at' => $now,
                'updated_at' => $now,
                'items' => [
                    ['product_name' => 'Carbonara',            'quantity' => 1, 'price' => 9.90,  'subtotal' => 9.90],
                    ['product_name' => 'Chicken Rice Bowl',    'quantity' => 1, 'price' => 8.90,  'subtotal' => 8.90],
                    ['product_name' => 'Turkish Tea',          'quantity' => 2, 'price' => 2.00,  'subtotal' => 4.00],
                ],
            ],
        ];

        // Build product name → id lookup
        $products = DB::table('products')->get(['id', 'name']);
        $productIds = [];
        foreach ($products as $p) {
            $productIds[$p->name] = $p->id;
        }

        foreach ($orders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);

            $orderId = DB::table('orders')->insertGetId($orderData);

            foreach ($items as $item) {
                $productId = $productIds[$item['product_name']] ?? null;
                if (! $productId) {
                    $this->command?->warn("Product not found for order item: {$item['product_name']}");

                    continue;
                }

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => $orderData['created_at'],
                ]);
            }
        }
    }
}
