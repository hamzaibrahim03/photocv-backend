<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      $this->call(RoleSeeder::class);
      $this->call(UserSeeder::class);
      $this->call(CatalogSeeder::class);
      $this->call([
        EventSeeder::class,
        CompetitionSeeder::class,
        NewsSeeder::class,
        PageSeeder::class,
        NoticeSeeder::class,
        MemberGallerySeeder::class,
        MemberSeeder::class,
        CommentsSeeder::class,
      ]);
    }
}
