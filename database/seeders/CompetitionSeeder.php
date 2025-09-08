<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\CompetitionJudge;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $competitions = [
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Open Competition',
                'description' => 'Showcase your best printed works.',
                'competition_type_id' => 1,
                'judging_type_id' => 1,
                'start_date' => Carbon::now()->addDays(10),
                'submission_deadline' => Carbon::now()->addDays(20),
                'max_entries_print' => 5,
                'max_entries_digital' => 0,
                'allowed_image_formats' => 'jpg,png',
                'max_file_size' => '5',
                'status' => 'scheduled',
                'category_id' => 1,
                'theme_id' => 1,
                'print_vs_digital' => 'print',
                'color_vs_mono' => 'color',
                'judging_panel' => 'Internal Panel',
                'voting_method_id' => 1,
                'result_method_id' => 1,
                'result_announcement_date' => Carbon::now()->addDays(30),
                'top_places' => 3,
                'high_commendation_number' => 2,
                'commendation_number' => 2,
                'prizes' => 'Trophies and Certificates',
                'cc_allowed' => true,
                'auto_certificate' => true,
                'allow_judges_feedback' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'judges' => [6,7] // attach judge IDs
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Line from a Song',
                'description' => 'A digital-only competition.',
                'competition_type_id' => 2,
                'judging_type_id' => 2,
                'start_date' => Carbon::now()->addDays(15),
                'submission_deadline' => Carbon::now()->addDays(25),
                'max_entries_print' => 0,
                'max_entries_digital' => 6,
                'allowed_image_formats' => 'jpg',
                'max_file_size' => '4',
                'status' => 'scheduled',
                'category_id' => 2,
                'theme_id' => 2,
                'print_vs_digital' => 'digital',
                'color_vs_mono' => 'color',
                'judging_panel' => 'Guest Judge',
                'voting_method_id' => 2,
                'result_method_id' => 2,
                'result_announcement_date' => Carbon::now()->addDays(35),
                'top_places' => 3,
                'high_commendation_number' => 1,
                'commendation_number' => 2,
                'prizes' => 'Gift Cards',
                'cc_allowed' => false,
                'auto_certificate' => true,
                'allow_judges_feedback' => false,
                'created_by' => 1,
                'updated_by' => 1,
                'judges' => [7,8] // attach judge IDs
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Nature Competition',
                'description' => 'Submit any creative work under an open theme.',
                'competition_type_id' => 1,
                'judging_type_id' => 3,
                'start_date' => Carbon::now()->addDays(5),
                'submission_deadline' => Carbon::now()->addDays(15),
                'max_entries_print' => 3,
                'max_entries_digital' => 3,
                'allowed_image_formats' => 'jpg,png,tiff',
                'max_file_size' => '10',
                'status' => 'scheduled',
                'category_id' => 3,
                'theme_id' => 3,
                'print_vs_digital' => 'print',
                'color_vs_mono' => 'color',
                'judging_panel' => 'Mixed Panel',
                'voting_method_id' => 3,
                'result_method_id' => 3,
                'result_announcement_date' => Carbon::now()->addDays(25),
                'top_places' => 5,
                'high_commendation_number' => 3,
                'commendation_number' => 3,
                'prizes' => 'Cash prizes and certificates',
                'cc_allowed' => true,
                'auto_certificate' => false,
                'allow_judges_feedback' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'judges' => [8,9] // multiple judges
            ],
        ];

        foreach ($competitions as $data) {
            $judges = $data['judges'] ?? [];
            unset($data['judges']);

            $competition = Competition::create($data);

            // Insert into competition_judges pivot
            foreach ($judges as $judgeId) {
                CompetitionJudge::create([
                    'competition_id' => $competition->id,
                    'user_id'       => $judgeId,
                ]);
            }
        }
    }
}
