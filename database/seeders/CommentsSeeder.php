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
        // Static member IDs (adjust if needed)
        $memberIds = [3, 4, 5, 6, 7, 8, 9];

        // Sample comment pool
        $sampleComments = [
            'Fantastic photo!',
            'Love the details.',
            'Amazing composition!',
            'Really impressive shot.',
            'Great use of colors.',
            'Wow, stunning!',
            'Nicely captured.',
            'Beautiful perspective!',
            'So creative!',
            'Sharp and vibrant photo!',
        ];

        // Add comments + likes for photo IDs 1 → 10 (adjust range as needed)
        for ($photoId = 1; $photoId <= 10; $photoId++) {
            // Add 2–3 comments
            $commentCount = rand(2, 3);
            for ($i = 0; $i < $commentCount; $i++) {
                DB::table('comments')->insert([
                    'record_id'     => $photoId,
                    'record_type'   => 'photo',
                    'comment_type'  => 'comment',
                    'comment'       => $sampleComments[array_rand($sampleComments)],
                    'interacted_by' => $memberIds[array_rand($memberIds)],
                    'is_published'  => true,
                    'admin_notes'   => null,
                    'created_at'    => now()->subDays(rand(0, 30)),
                    'updated_at'    => now(),
                ]);
            }

            // Add 1–4 likes
            $likeCount = rand(1, 4);
            for ($i = 0; $i < $likeCount; $i++) {
                DB::table('comments')->insert([
                    'record_id'     => $photoId,
                    'record_type'   => 'photo',
                    'comment_type'  => 'liking',
                    'comment'       => null,
                    'interacted_by' => $memberIds[array_rand($memberIds)],
                    'is_published'  => true,
                    'admin_notes'   => null,
                    'created_at'    => now()->subDays(rand(0, 30)),
                    'updated_at'    => now(),
                ]);
            }
        }

        // Keep your old hardcoded examples (news, event, notice)
        $records = [
            [
                'record_id'     => 2,
                'record_type'   => 'news',
                'comment_type'  => 'comment',
                'comment'       => 'Very informative news.',
                'interacted_by' => $memberIds[1],
            ],
            [
                'record_id'     => 3,
                'record_type'   => 'event',
                'comment_type'  => 'liking',
                'interacted_by' => $memberIds[0],
            ],
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
