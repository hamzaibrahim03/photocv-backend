<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gear;
use App\Models\User;
use App\Models\Catalog;

class GearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'photographer@photocv.com'],
            ['name' => 'Photographer', 'password' => bcrypt('secret123')]
        );

        // find a category in catalog (make sure CatalogSeeder has run)
        $cameraCategory = Catalog::where('catalog_type', 'gear_category')
                                ->where('name', 'Camera')
                                ->first();

        $lensCategory = Catalog::where('catalog_type', 'gear_category')
                                ->where('name', 'Lens')
                                ->first();

        // Example gears
        $gears = [
            [
                'title' => 'Canon EOS R5',
                'brand' => 'Canon',
                'model' => 'EOS R5',
                'category_id' => $cameraCategory->id,
                'description' => 'High-resolution mirrorless camera',
                'purchase_date' => '2023-01-15',
                'purchase_price' => 3899.99,
                'vendor' => 'B&H Photo',
                'serial_number' => 'CNR5-12345',
                'ownership_type' => 'Owned',
                'warranty_expiry' => '2026-01-15',
                'sensor_type' => 'Full-frame CMOS',
                'resolution' => '45MP',
                'suitability_wedding' => 90,
                'suitability_portrait' => 85,
                'suitability_landscape' => 95,
                'condition' => 'New',
            ],
            [
                'title' => 'Sony A7 IV',
                'brand' => 'Sony',
                'model' => 'ILCE-7M4',
                'category_id' => $cameraCategory->id,
                'description' => 'Versatile hybrid camera',
                'purchase_date' => '2022-09-10',
                'purchase_price' => 2499.00,
                'vendor' => 'Adorama',
                'serial_number' => 'SN-A7IV-6789',
                'ownership_type' => 'Owned',
                'warranty_expiry' => '2025-09-10',
                'sensor_type' => 'Full-frame BSI CMOS',
                'resolution' => '33MP',
                'suitability_travel' => 90,
                'suitability_event' => 85,
                'suitability_wildlife' => 80,
                'condition' => 'Good',
            ],
            [
                'title' => 'Canon RF 24-70mm f/2.8',
                'brand' => 'Canon',
                'model' => 'RF 24-70mm',
                'category_id' => $lensCategory->id,
                'description' => 'Fast professional zoom lens',
                'purchase_date' => '2021-05-20',
                'purchase_price' => 2299.00,
                'vendor' => 'Canon Store',
                'serial_number' => 'RF2470-24680',
                'ownership_type' => 'Owned',
                'warranty_expiry' => '2024-05-20',
                'focal_length' => '24-70mm',
                'aperture' => 'f/2.8',
                'suitability_wedding' => 95,
                'suitability_event' => 90,
                'suitability_portrait' => 85,
                'condition' => 'Good',
            ],
            [
                'title' => 'Godox AD600 Pro',
                'brand' => 'Godox',
                'model' => 'AD600 Pro',
                'category_id' => $lensCategory->id,
                'description' => 'Powerful portable strobe light',
                'purchase_date' => '2022-07-01',
                'purchase_price' => 899.00,
                'vendor' => 'Amazon',
                'serial_number' => 'GD600-11223',
                'ownership_type' => 'Rented',
                'warranty_expiry' => '2024-07-01',
                'suitability_wedding' => 80,
                'condition' => 'Good',
            ],
        ];

        foreach ($gears as $gear) {
            Gear::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'title'   => $gear['title'],
                ],
                $gear + ['user_id' => $user->id]
            );
        }
    }
}
