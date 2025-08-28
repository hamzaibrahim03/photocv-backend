<?php

namespace Database\Seeders;

use App\Models\MemberNotice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            [
                'club_id' => 1,
                'featured_image' => null,
                'member_id' => 1,
                'notice_type_id' => 1, // General Notice
                'title' => 'Item for sale',
                'description' => 'Join us for our regular monthly club meeting.',
                'tags' => 'meeting,club',
                'location' => 'Main Hall, Club House',
                'link_page_url' => 'https://example.com/meeting-details',
                'status' => 'active',
                'poll' => 'public',
                'urgency_importance' => 'general',
                'comment_allowed' => 'none',
                'is_active' => true,
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'member_id' => 1,
                'notice_type_id' => 2, // Urgent Notice
                'title' => 'Change in Venue for Photography Walk',
                'description' => 'Due to weather, the walk will now start from Central Park entrance.',
                'tags' => 'photography,update',
                'location' => 'Central Park, Main Gate',
                'link_page_url' => 'https://example.com/venue-change',
                'status' => 'active',
                'poll' => 'public',
                'urgency_importance' => 'general',
                'comment_allowed' => 'none',
                'is_active' => true,
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'member_id' => 2,
                'notice_type_id' => 1,
                'title' => 'Poll: Select Theme for Next Competition',
                'description' => 'Vote for your favorite theme for next month’s competition.',
                'tags' => 'poll,competition',
                'location' => null,
                'link_page_url' => 'https://example.com/theme-poll',
                'status' => 'active',
                'poll' => 'public',
                'urgency_importance' => 'general',
                'comment_allowed' => 'none',
                'is_active' => true,
            ],
        ];

        foreach ($notices as $notice) {
            MemberNotice::create($notice);
        }
    }
}
