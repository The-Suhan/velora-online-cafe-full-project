<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $feedbacks = [
            [
                'user_id' => 1,
                'type' => 'complaint',
                'subject' => 'Order arrived late',
                'message' => 'My last delivery order took over an hour. The food was cold when it arrived. Please improve delivery times.',
                'is_done' => true,
                'done_by' => null,
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(7),
            ],
            [
                'user_id' => 1,
                'type' => 'request',
                'subject' => 'Add more vegan options',
                'message' => 'I would love to see more variety in the vegan section. Maybe a vegan burger with a plant-based patty and more vegan dessert options.',
                'is_done' => false,
                'done_by' => null,
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => null,
            ],
            [
                'user_id' => 1,
                'type' => 'question',
                'subject' => 'Allergen information',
                'message' => 'Do your pizzas contain gluten? I have a wheat allergy and would like to know if there are gluten-free crust options available.',
                'is_done' => false,
                'done_by' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => null,
            ],
        ];

        foreach ($feedbacks as $feedback) {
            DB::table('feedbacks')->insert($feedback);
        }
    }
}
