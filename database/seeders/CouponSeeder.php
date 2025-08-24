<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Coupon::create([
            'code' => 'WELCOME10',
            'discount_percentage' => 10.00,
            'is_active' => true,
            'expiry_date' => now()->addMonths(3),
            'usage_limit' => 100,
            'usage_count' => 0
        ]);

        \App\Models\Coupon::create([
            'code' => 'SUMMER25',
            'discount_percentage' => 25.00,
            'is_active' => true,
            'expiry_date' => now()->addDays(30),
            'usage_limit' => 50,
            'usage_count' => 0
        ]);

        \App\Models\Coupon::create([
            'code' => 'CHESS15',
            'discount_percentage' => 15.00,
            'is_active' => true,
            'expiry_date' => null, // No expiry
            'usage_limit' => null, // Unlimited usage
            'usage_count' => 0
        ]);
    }
}
