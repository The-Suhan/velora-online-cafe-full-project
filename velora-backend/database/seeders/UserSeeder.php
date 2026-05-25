<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([
            // ── id = 1  ──────────
            [
                'name' => 'Suhan',
                'email' => 'begenjov@velora.com',
                'password' => Hash::make('suhanberdi'),
                'role' => 'admin',
                'is_verified' => true,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // ── id = 2 ────────────────────────────────────────────────────
            [
                'name' => 'Akmuhammet',
                'email' => 'Akmyradow@velora.com',
                'password' => Hash::make('VeloraOps'),
                'role' => 'admin',
                'is_verified' => true,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // ── id = 3 ────────────────────────────────────────────────────
            [
                'name' => 'Oraz',
                'email' => 'chollaev@velora.com',
                'password' => Hash::make('VeloraOps'),
                'role' => 'admin',
                'is_verified' => true,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
