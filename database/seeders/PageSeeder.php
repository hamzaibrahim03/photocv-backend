<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'club_id' => 1,
                'title' => 'About Us',
                'publish_date' => Carbon::now(),
                'description' => 'Learn more about our club, our vision, and our team.',
                'featured_image' => 'pages/about.jpg',
                'page_type_id' => 1, // Assuming 1 = General Pages
                'page_slug' => Str::slug('About Us'),
                'status' => 'publish',
            ],
            [
                'club_id' => 1,
                'title' => 'Terms and Conditions',
                'publish_date' => Carbon::now()->addDays(1),
                'description' => 'Please read our terms and conditions carefully before participating.',
                'featured_image' => 'pages/terms.jpg',
                'page_type_id' => 1,
                'page_slug' => Str::slug('Terms and Conditions'),
                'status' => 'publish',
            ],
            [
                'club_id' => 1,
                'title' => 'Privacy Policy',
                'publish_date' => Carbon::now()->addDays(2),
                'description' => 'We value your privacy. This page explains how we handle your data.',
                'featured_image' => 'pages/privacy.jpg',
                'page_type_id' => 1,
                'page_slug' => Str::slug('Privacy Policy'),
                'status' => 'draft',
            ],
        ];

        foreach ($pages as $data) {
            Page::create($data);
        }
    }
}
