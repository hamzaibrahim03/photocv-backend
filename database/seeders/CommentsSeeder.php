<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Simulated member user IDs — adjust as needed
        $memberIds = [5, 6, 7];

        $records = [
            // Comments
            [
                'record_id'     => 1,
                'record_type'   => 'photo',
                'comment_type'  => 'comment',
                'comment'       => 'Fantastic photo!',
                'interacted_by' => $memberIds[0],
            ],
            [
                'record_id'     => 2,
                'record_type'   => 'news',
                'comment_type'  => 'comment',
                'comment'       => 'Very informative news.',
                'interacted_by' => $memberIds[1],
            ],
            // Likes
            [
                'record_id'     => 1,
                'record_type'   => 'photo',
                'comment_type'  => 'liking',
                'interacted_by' => $memberIds[2],
            ],
            [
                'record_id'     => 3,
                'record_type'   => 'event',
                'comment_type'  => 'liking',
                'interacted_by' => $memberIds[0],
            ],
            // Another comment
            [
                'record_id'     => 2,
                'record_type'   => 'notice',
                'comment_type'  => 'comment',
                'comment'       => 'Thanks for the update!',
                'interacted_by' => $memberIds[2],
            ],
        ];

        foreach ($records as $item) {
            DB::table('comments')->insert([
                'record_id'     => $item['record_id'],
                'record_type'   => $item['record_type'],
                'comment_type'  => $item['comment_type'],
                'comment'       => $item['comment'] ?? null,
                'interacted_by' => $item['interacted_by'],
                'is_published'  => true,
                'admin_notes'   => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
