<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Club;
use App\Models\ClubSetting;
use Spatie\Permission\Models\Role;
use App\Models\Member;
use App\Models\MemberBrand;
use App\Models\MemberContact;
use App\Models\MemberSocialLink;
use App\Models\MemberCoverImage;


class SignUpRepository implements SignUpRepositoryInterface
{
    /**
     * Create a new user and assign a role.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        // Create user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'tag_line' => $data['tag_line'] ?? null,
            'about' => $data['about'] ?? null,
        ]);

        // Assign role
        if (!empty($data['role'])) {
            $role = Role::findByName($data['role']);
            $user->assignRole($role);
        }

        if (!empty($data['club'])) {
            $clubData = $data['club'];
            $clubData['user_id'] = $user->id;

            // Fields that belong to club_settings, not clubs — pulled out
            // before Club::create() so mass assignment doesn't silently
            // drop them (Club's $fillable only covers identity fields).
            $clubSettingFields = [
                'registration',
                'directory_visibility',
                'comments',
                'likes',
                'reminders',
                'website_sections',
                'comment_preference',
                'fb_link',
                'fb_link_option',
                'insta_link',
                'insta_link_option',
                'flickr_link',
                'flickr_link_option',
            ];

            $clubSettingData = [];
            foreach ($clubSettingFields as $field) {
                if (isset($clubData[$field])) {
                    $clubSettingData[$field] = $clubData[$field];
                    unset($clubData[$field]); // remove from clubData
                }
            }

            // Logo / banner uploads go to club_settings (logo, club_banner),
            // not clubs — Club has no columns for either.
            if (!empty($clubData['logo'])) {
                $clubSettingData['logo'] = $clubData['logo']->store('uploads/clubs/logo', 'public');
            }
            unset($clubData['logo']);

            if (!empty($clubData['banner'])) {
                $clubSettingData['club_banner'] = $clubData['banner']->store('uploads/clubs/banner', 'public');
            }
            unset($clubData['banner']);

            // Columns with no default and no NOT NULL fallback in the
            // schema — the signup wizard has no UI for club_privacy yet,
            // so default it rather than letting the insert fail.
            $clubSettingData['club_privacy'] = $clubSettingData['club_privacy'] ?? 'Public';
            $clubSettingData['website_sections'] = $clubSettingData['website_sections'] ?? 'News';
            $clubSettingData['comment_preference'] = $clubSettingData['comment_preference'] ?? 'All';

            // Create club
            $club = Club::create($clubData);

            $clubSettingData['club_id'] = $club->id;
            ClubSetting::create($clubSettingData);
        }

        // Create Member record
            $member = Member::create([
                'user_id'               => $user->id,
                'domain_name'           => $data['member']['domain_name'] ?? null,
                'domain_type'           => $data['member']['domain_type'] ?? null,
                'color_theme'           => $data['member']['color_theme'] ?? null,
                'fonts'                  => $data['member']['fonts'] ?? null,
                'logo'                  => $data['member']['logo'] ?? null,
                'profile_privacy'       => $data['member']['profile_privacy'] ?? 'public',
                'header_text'           => $data['member']['header_text'] ?? null,
                'footer_text'           => $data['member']['footer_text'] ?? null,
                'social_links_visibility'=> $data['member']['social_links_visibility'] ?? [],
            ]);

            // Multiple cover images (strings/paths)
            if (!empty($data['member']['cover_images']) && is_array($data['member']['cover_images'])) {
                foreach (array_values($data['member']['cover_images']) as $idx => $imagePath) {
                    MemberCoverImage::create([
                        'member_id'  => $member->id,
                        'image_path' => $imagePath,
                        'position'   => $idx,
                    ]);
                }
            }

            // If you expect file uploads instead of paths, use this instead:
            /*
            if (!empty($data['member']['cover_images']) && is_array($data['member']['cover_images'])) {
                foreach (array_values($data['member']['cover_images']) as $idx => $uploadedFile) {
                    $stored = $uploadedFile->store('uploads/members/covers', 'public');
                    MemberCoverImage::create([
                        'member_id'  => $member->id,
                        'image_path' => $stored,
                        'position'   => $idx,
                    ]);
                }
            }
            */

            // MemberBrands
            if (!empty($data['member_brands'])) {
                $memberId = $member->id;

                if (!empty($data['member_brands']['interest'])) {
                    foreach ($data['member_brands']['interest'] as $interest) {
                        MemberBrand::create([
                            'member_id' => $memberId,
                            'interest'  => $interest,
                            'brands'    => null,
                        ]);
                    }
                }
                if (!empty($data['member_brands']['brands'])) {
                    foreach ($data['member_brands']['brands'] as $brand) {
                        MemberBrand::create([
                            'member_id' => $memberId,
                            'interest'  => null,
                            'brands'    => $brand,
                        ]);
                    }
                }
            }

            // MemberContact
            if (!empty($data['member_contact'])) {
                MemberContact::create([
                    'member_id' => $member->id,
                    'email'     => $data['member_contact']['email'] ?? null,
                    'phone'     => $data['member_contact']['phone'] ?? null,
                    'address'   => $data['member_contact']['address'] ?? null,
                ]);
            }

            // Social links
            if (!empty($data['member_social_media'])) {
                foreach ($data['member_social_media'] as $social) {
                    MemberSocialLink::create([
                        'member_id'          => $member->id,
                        'social_media_name'  => $social['social_media_name'],
                        'social_link'        => $social['social_link'],
                    ]);
                }
            }

        return $user;
    }

    /**
     * Check if a user with the given email or username already exists.
     *
     * @param string $email
     * @param string $username
     * @return \Illuminate\Http\JsonResponse
     */
    public function exists(string $email, string $username)
    {
        // Check if a user with the given email or username already exists
        $user = User::where('email', $email)->orWhere('username', $username)->exists();
        if ($user) {
            return response()->json(['message' => 'User already registered.'], 200);
        } else {
            return response()->json(['message' => 'User not registered.'], 404);
        }
    }

    /**
     * Check if a domain is already registered.
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function alreadyRegisteredDomain(array $data)
    {
        // Check if a user with the given domain already exists
        $domain = $data['domain'];
        $domain = User::where('domain_name', 'like', '%' . $domain)->exists();
        
        if ($domain) {
            return response()->json(['message' => 'Domain already registered.'], 200);
        } else {
            return response()->json(['message' => 'Domain not registered.'], 404);
        }
    }
}
