<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run()
    {
        $catalogs = [
            ['name' => 'Photography Competition', 'catalog_type' => 'competition_type', 'icon' => 'photography.png'],
            ['name' => 'Music Competition', 'catalog_type' => 'competition_type', 'icon' => 'music.png'],
            ['name' => 'Workshop', 'catalog_type' => 'event_type', 'icon' => 'workshop.png'],
            ['name' => 'Conference', 'catalog_type' => 'event_type', 'icon' => 'conference.png'],
            ['name' => 'Online', 'catalog_type' => 'event_tag', 'icon' => 'online.png'],
            ['name' => 'Offline', 'catalog_type' => 'event_tag', 'icon' => 'offline.png'],
            ['name' => 'Panel Judging', 'catalog_type' => 'judging_type', 'icon' => 'panel.png'],
            ['name' => 'Public Voting', 'catalog_type' => 'judging_type', 'icon' => 'public.png'],
            ['name' => 'Landscape', 'catalog_type' => 'competition_theme', 'icon' => 'landscape.png'],
            ['name' => 'Portrait', 'catalog_type' => 'competition_theme', 'icon' => 'portrait.png'],
            ['name' => 'Photography', 'catalog_type' => 'competition_catagories', 'icon' => 'photo.png'],
            ['name' => 'Painting', 'catalog_type' => 'competition_catagories', 'icon' => 'painting.png'],
            ['name' => 'Jury Voting', 'catalog_type' => 'competition_voting_method', 'icon' => 'jury.png'],
            ['name' => 'Public Votes', 'catalog_type' => 'competition_voting_method', 'icon' => 'public_votes.png'],
            ['name' => 'Point System', 'catalog_type' => 'competition_result_method', 'icon' => 'points.png'],
            ['name' => 'Ranked Choice', 'catalog_type' => 'competition_result_method', 'icon' => 'ranked.png'],
            ['name' => 'General Notice', 'catalog_type' => 'notice_type', 'icon' => 'general.png'],
            ['name' => 'Urgent Notice', 'catalog_type' => 'notice_type', 'icon' => 'urgent.png'],
            ['name' => 'Urgent News', 'catalog_type' => 'news_type', 'icon' => 'urgent.png'],
            ['name' => 'General News', 'catalog_type' => 'news_type', 'icon' => 'urgent.png'],
            ['name' => 'General Pages', 'catalog_type' => 'page_type', 'icon' => 'urgent.png'],
        ];

        DB::table('catalog')->insert($catalogs);
    }
}

