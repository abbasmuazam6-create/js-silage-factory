<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seasons
        $seasons = [
            ['name' => 'Kharif (Season 1)', 'start_month' => 1, 'start_day' => 1, 'end_month' => 6, 'end_day' => 30, 'color' => '#22c55e', 'is_current' => true],
            ['name' => 'Rabi (Season 2)', 'start_month' => 7, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31, 'color' => '#3b82f6', 'is_current' => false],
        ];

        foreach ($seasons as $season) {
            \App\Models\Season::create([
                'id' => Str::uuid(),
                'name' => $season['name'],
                'start_month' => $season['start_month'],
                'start_day' => $season['start_day'],
                'end_month' => $season['end_month'],
                'end_day' => $season['end_day'],
                'color' => $season['color'],
                'is_current' => $season['is_current'],
            ]);
        }

        $categories = [
            ['name' => 'Labour', 'color' => '#EF4444'],
            ['name' => 'Transport', 'color' => '#F59E0B'],
            ['name' => 'Machinery', 'color' => '#3B82F6'],
            ['name' => 'Utilities', 'color' => '#10B981'],
            ['name' => 'Maintenance', 'color' => '#8B5CF6'],
            ['name' => 'Other', 'color' => '#6B7280'],
        ];
        foreach ($categories as $cat) {
            \App\Models\ExpenseCategory::create([
                'id' => Str::uuid(),
                'name' => $cat['name'],
                'color' => $cat['color'],
            ]);
        }

        // Admin user (if not already created)
        if (!\App\Models\User::where('email', 'admin@jssilage.com')->exists()) {
            \App\Models\User::create([
                'id' => Str::uuid(),
                'name' => 'Admin',
                'email' => 'admin@jssilage.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'api_token' => Str::random(80),
                'is_active' => true,
            ]);
        }
    }
}