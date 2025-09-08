<?php

namespace Database\Seeders;

use App\Models\ClubNews;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $newsItems = [
            [
                'club_id' => 1,
                'title' => 'NCPF’s Beamish Trophy 2024',
                'news_type_id' => 1, // Assuming 1 = Urgent News
                'short_description' => 'Join us for our biggest annual gathering.',
                'description' => 'Our annual meetup will feature workshops, networking, and awards. Don’t miss it!',
                'featured_image' => 'news/meetup.jpg',
                'publish_date' => Carbon::now()->addDays(2),
            ],
            [
                'club_id' => 1,
                'title' => 'External Competition Secretary’s Report',
                'news_type_id' => 2, // Assuming 2 = General News
                'short_description' => 'A new photography competition is launching soon.',
                'description' => 'Get ready to submit your best work! More details will be shared in the coming days.',
                'featured_image' => 'news/competition.jpg',
                'publish_date' => Carbon::now()->addDays(5),
            ],
            [
                'club_id' => 1,
                'title' => 'South Tyne Print Competition',
                'news_type_id' => 1, // Urgent News
                'short_description' => 'Please review the latest changes in club rules.',
                'description' => 'We have updated our policy regarding event participation and photo submissions. Read the full post for details.',
                'featured_image' => 'news/policy.jpg',
                'publish_date' => Carbon::now()->addDays(1),
            ],
        ];

        foreach ($newsItems as $item) {
            ClubNews::create($item);
        }
    }
}
