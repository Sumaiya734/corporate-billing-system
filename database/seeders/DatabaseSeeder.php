<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // System Settings
        DB::table('system_settings')->insert([
            [
                'key' => 'fixed_monthly_charge',
                'value' => '50',
                'description' => 'Fixed monthly charge for all customers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'vat_percentage', 
                'value' => '7',
                'description' => 'VAT percentage applied to invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ... other settings
        ]);

        // Packages
        DB::table('packages')->insert([
            // Regular Packages
            [
                'name' => 'Basic Speed',
                'type' => 'regular',
                'price' => 500.00,
                'description' => 'Basic internet for everyday browsing',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ... other packages
        ]);

        // Create Admin User
        DB::table('users')->insert([
            'name' => 'NetBill Admin',
            'email' => 'admin@netbillbd.com',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Test Customer
        DB::table('customers')->insert([
            'customer_id' => 'CUST-' . strtoupper(uniqid()),
            'name' => 'John Doe',
            'email' => 'customer@netbillbd.com',
            'phone' => '+8801XXXXXXXXX',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('customer123'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}