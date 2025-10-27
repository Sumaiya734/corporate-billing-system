<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run()
    {
        $packages = [
            // Regular Packages
            [
                'name' => 'Basic Speed',
                'type' => 'regular',
                'price' => 500.00,
                'description' => 'Basic internet for everyday browsing'
            ],
            [
                'name' => 'Fast Speed', 
                'type' => 'regular',
                'price' => 800.00,
                'description' => 'Fast internet for streaming and downloads'
            ],
            [
                'name' => 'Super Speed',
                'type' => 'regular', 
                'price' => 1200.00,
                'description' => 'Super fast internet for gaming and 4K streaming'
            ],
            
            // Special Packages
            [
                'name' => 'Gaming Boost',
                'type' => 'special',
                'price' => 200.00,
                'description' => 'Low latency for gaming'
            ],
            [
                'name' => 'Streaming Plus',
                'type' => 'special',
                'price' => 150.00,
                'description' => 'Optimized for HD streaming'
            ],
            [
                'name' => 'Family Pack',
                'type' => 'special',
                'price' => 300.00,
                'description' => 'Multiple device connectivity'
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}