<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubSetting;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\ClubCoverImage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@photocv.com'],
            [
                'username' => 'superadmin',
                'password'   => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');


        // Create another Club Admin with a club
        $ryton = User::firstOrCreate(
            ['email' => 'ryton@cameraclub.website'],
            [
                'username' => 'rytonlocal',
                'first_name' => 'ryton',
                'last_name' => 'club',
                'password' => Hash::make('secret123'),
                'phone' => '+44 123-456-789',
                'address' => 'Cross House Community Centre, Ryton Village, Tyne & Wear, NE40 3QP',
            ]
        );
        $ryton->assignRole('club_admin');

        // Create Club for ryton if not already exists
        $club = Club::firstOrCreate(
            ['user_id' => $ryton->id],
            [
                'club_name'       => 'Ryton Camera Club',
                'tag_line'        => 'A camera club since September 1950',
                'about'           => 'Reach your true photographic potential',
                'contact_details' => 'sedgwickphotography@gmail.com',
                'domain_type'     => 'subdomain',
                'domain_name'     => 'ryton.cameraclub.website',
                'created_by'      => $ryton->id,
            ]
        );

        // Create Club Settings for John's club
        $clubSetting = ClubSetting::firstOrCreate(
            ['club_id' => $club->id],
            [
                'timezone'                          => 'UK',
                'date'                              => now()->toDateString(),
                'club_privacy'                      => 'Public',
                'theme_colors'                      => 'Dark',
                'text_color'                        => '#333333',
                'primary_color'                     => '#7FA483',
                'background_color'                  => '#FFFFFF',
                'secondary_color'                   => '#ECEDE6',
                'accent_color'                      => '#DD9757',
                'typography'                        => 'Inter',
                'fonts'                             => 'Inter',
                'header_title'                      => 'About Ryton Camera Club',
                'header_description'                => 'Ryton Camera Club is based in the Ryton village, 6 miles west of Gateshead. We are a friendly group, with a range of experience, who enjoy all aspects of photography. We welcome new members of all abilities, whether you’re a seasoned pro or a complete novice.<br><br>We run club competitions on a regular basis. They provide a good opportunity for members to have their images critiqued by trained judges. This is a good way to learn how to improve your photography skills, but you are under no obligation to enter. The club also enters inter-club competitions. In recent years we have won both the South Tyne Area PDI (Projected Digital Images) and Print competitions. As the club is affiliated to the NCPF (Northern Counties Photographic Federation), we also compete with other clubs from across the whole of Northern England. ',
                'header_img'                        => 'uploads/clubs/header/default.png',
                'footer_text'                       => 'About Our Club',
                'footer_img'                        => 'uploads/clubs/footer/default.png',
                'footer_description'                => 'Ryton Camera Club is based in the Ryton village, 6 miles west of Gateshead. We are a friendly group, with a range of experience, who enjoy all aspects of photography. We welcome new members of all abilities, whether you’re a seasoned pro or a complete novice.',
                'logo'                              => 'club_logos/default.png',
                'registration'                      => 'open',
                'directory_visibility'              => 'club_only',
                'comments'                          => 'enabled',
                'likes'                             => 'enabled',
                'website_sections'                  => 'News',
                'comment_preference'                => 'all',
                'reminders'                         => 'all',
                'fb_link_option'                    => 1,
                'fb_link'                           => 'https://www.facebook.com/groups/928325923907903',
                'insta_link_option'                 => 1,
                'insta_link'                        => 'https://www.instagram.com/rytoncc/',
                'flickr_link_option'                => 1,
                'flickr_link'                       => 'https://www.flickr.com/groups/rytoncc/',
                'gdpr_privacy_policy_management'    => 'Test',
                'cookies'                           => 1,
                'cookies_description'               => 'Test',
                'data_collection_preferences'       => 1,
                'data_collection_preferences_description' => 'Test',
                'allow_reporting'                   => 1,
                'allow_reporting_description'       => 'Test',
            ]
        );

        /**
         * === Upload Images from Seeder Data Folder ===
         * Copy your files into: database/seeders/data/ryton-club-data/
         * ├── logo/logo.svg
         * └── cover_images/image1.jpg, image2.jpg, ...
         */
        $basePath = database_path('seeders/data/ryton-club-data');

        // Upload Logo
        $logoPath = $basePath . '/logo/logo.svg';
        if (file_exists($logoPath)) {
            $logoTarget = 'clubs/logo/' . basename($logoPath);
            Storage::disk('public')->put($logoTarget, file_get_contents($logoPath));
            $clubSetting->update(['logo' => $logoTarget]);
        }

        // Upload Cover Images
        $coverFolder = $basePath . '/cover_images';
        if (is_dir($coverFolder)) {
            foreach (glob($coverFolder . '/*.*') as $coverImagePath) {
                $targetPath = 'clubs/cover/' . basename($coverImagePath);
                Storage::disk('public')->put($targetPath, file_get_contents($coverImagePath));

                ClubCoverImage::firstOrCreate([
                    'club_setting_id' => $clubSetting->id,
                    'image_path'      => $targetPath,
                ]);
            }
        }

        // === Upload Header Image ===
        $headerPath = $basePath . '/header/header.jpg';
        if (file_exists($headerPath)) {
            $headerTarget = 'clubs/header/' . basename($headerPath);
            Storage::disk('public')->put($headerTarget, file_get_contents($headerPath));
            $clubSetting->update(['header_img' => $headerTarget]);
        }

        // === Upload Footer Image ===
        $footerPath = $basePath . '/footer/footer.jpg';
        if (file_exists($footerPath)) {
            $footerTarget = 'clubs/footer/' . basename($footerPath);
            Storage::disk('public')->put($footerTarget, file_get_contents($footerPath));
            $clubSetting->update(['footer_img' => $footerTarget]);
        }
    }
}
