<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'name' => 'Admin',
            'email' => 'admin@jssilage.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        public function run(): void
        {
            $this->call(TestDataSeeder::class);
        }
        // 2. Default Settings (with UUIDs)
        DB::table('settings')->insert([
            ['id' => Str::uuid(), 'key' => 'business_name', 'value' => 'JS Silage & Wanda Factory', 'description' => 'Business name displayed on invoices and reports'],
            ['id' => Str::uuid(), 'key' => 'business_address', 'value' => 'ABC Farm, City, Country', 'description' => 'Business address'],
            ['id' => Str::uuid(), 'key' => 'phone', 'value' => '0300-1234567', 'description' => 'Business phone number'],
            ['id' => Str::uuid(), 'key' => 'email', 'value' => 'info@jssilage.com', 'description' => 'Business email'],
            ['id' => Str::uuid(), 'key' => 'currency_symbol', 'value' => '$', 'description' => 'Currency symbol'],
            ['id' => Str::uuid(), 'key' => 'tax_rate', 'value' => '0', 'description' => 'Tax rate in percentage'],
            ['id' => Str::uuid(), 'key' => 'bunker_threshold_percentage', 'value' => '10', 'description' => 'Percentage at which bunker triggers warning'],
            ['id' => Str::uuid(), 'key' => 'wanda_low_stock_alert_kg', 'value' => '100', 'description' => 'Minimum stock level for Wanda'],
            ['id' => Str::uuid(), 'key' => 'invoice_prefix', 'value' => 'INV-', 'description' => 'Prefix for invoice numbers'],
            ['id' => Str::uuid(), 'key' => 'payment_prefix', 'value' => 'RCP-', 'description' => 'Prefix for receipt numbers'],
            ['id' => Str::uuid(), 'key' => 'purchase_prefix', 'value' => 'PUR-', 'description' => 'Prefix for purchase numbers'],
            ['id' => Str::uuid(), 'key' => 'wanda_batch_prefix', 'value' => 'BATCH-', 'description' => 'Prefix for Wanda batch numbers'],
            ['id' => Str::uuid(), 'key' => 'date_format', 'value' => 'DD/MM/YYYY', 'description' => 'Date format used throughout the app'],
        ]);

        // 3. Default Expense Categories
        DB::table('expense_categories')->insert([
            ['id' => Str::uuid(), 'name' => 'Labor', 'available_in' => json_encode(['silage', 'wanda', 'general'])],
            ['id' => Str::uuid(), 'name' => 'Packaging', 'available_in' => json_encode(['silage', 'wanda'])],
            ['id' => Str::uuid(), 'name' => 'Transport', 'available_in' => json_encode(['silage', 'wanda', 'general'])],
            ['id' => Str::uuid(), 'name' => 'Machinery', 'available_in' => json_encode(['silage', 'wanda'])],
            ['id' => Str::uuid(), 'name' => 'Utilities', 'available_in' => json_encode(['general'])],
            ['id' => Str::uuid(), 'name' => 'Rent', 'available_in' => json_encode(['general'])],
            ['id' => Str::uuid(), 'name' => 'Other', 'available_in' => json_encode(['silage', 'wanda', 'general'])],
        ]);

        // 4. Default Payment Types
        DB::table('payment_types')->insert([
            ['id' => Str::uuid(), 'name' => 'Cash'],
            ['id' => Str::uuid(), 'name' => 'Bank Transfer'],
            ['id' => Str::uuid(), 'name' => 'Cheque'],
            ['id' => Str::uuid(), 'name' => 'JazzCash'],
            ['id' => Str::uuid(), 'name' => 'Easypaisa'],
        ]);
        // 5. Default Seasons
        DB::table('seasons')->insert([
            [
                'id' => Str::uuid(),
                'name' => 'Kharif (Season 1)',
                'start_month' => 1,
                'start_day' => 1,
                'end_month' => 6,
                'end_day' => 30,
                'color' => '#22c55e',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Rabi (Season 2)',
                'start_month' => 7,
                'start_day' => 1,
                'end_month' => 12,
                'end_day' => 31,
                'color' => '#0ea5e9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
