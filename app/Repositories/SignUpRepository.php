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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

            // Handle about_img
            if (!empty($data['about_img'])) {
                $aboutImg = $data['about_img'];
                $clubData['about_img'] = $aboutImg->store('uploads/clubs/about', 'public');
            }

            // Handle footer_img
            if (!empty($data['footer_img'])) {
                $footerImg = $data['footer_img'];
                $clubData['footer_img'] = $footerImg->store('uploads/clubs/footer', 'public');
            }

            // Extract ClubSetting fields before creating Club
            $clubSettingFields = [
                'registration',
                'directory_visibility',
                'comments',
                'likes',
                'reminders',
            ];

            $clubSettingData = [];
            foreach ($clubSettingFields as $field) {
                if (isset($clubData[$field])) {
                    $clubSettingData[$field] = $clubData[$field];
                    unset($clubData[$field]); // remove from clubData
                }
            }

            // Create club
            $club = Club::create($clubData);

            // Create club settings if any were provided
            if (!empty($clubSettingData)) {
                $clubSettingData['club_id'] = $club->id;
                ClubSetting::create($clubSettingData);
            }
        }

        // Create Member record
        if (!empty($data['member'])) {
            $member = Member::create([
                'user_id' => $user->id,
                'domain_name' => $data['member']['domain_name'] ?? null,
                'color_theme' => $data['member']['color_theme'] ?? null,
                // 'cover_image' => $data['member']['cover_image'] ?? null,
                'font' => $data['member']['font'] ?? null,
                'profile_privacy' => $data['member']['profile_privacy'] ?? 'public',
                'footer_text' => $data['member']['footer_text'] ?? null,
                'social_links_visibility' => $data['member']['social_links_visibility'] ?? [],
            ]);
        }

        // Create MemberBrands record
        if (!empty($data['member_brands'])) {
            $memberId = $member->id ?? null;

            // Loop through interests
            if (!empty($data['member_brands']['interest'])) {
                foreach ($data['member_brands']['interest'] as $interest) {
                    MemberBrand::create([
                        'member_id' => $memberId,
                        'interest' => $interest,
                        'brands' => null,
                    ]);
                }
            }

            // Loop through brands
            if (!empty($data['member_brands']['brands'])) {
                foreach ($data['member_brands']['brands'] as $brand) {
                    MemberBrand::create([
                        'member_id' => $memberId,
                        'interest' => null,
                        'brands' => $brand,
                    ]);
                }
            }
        }

        // Create MemberContact record
        if (!empty($data['member_contact'])) {
            MemberContact::create([
                'member_id' => $member->id ?? null,
                'email' => $data['member_contact']['email'] ?? null,
                'phone' => $data['member_contact']['phone'] ?? null,
                'address' => $data['member_contact']['address'] ?? null,
            ]);
        }

        // Create MemberSocialLink records
        if (!empty($data['member_social_media'])) {
            foreach ($data['member_social_media'] as $social) {
                MemberSocialLink::create([
                    'member_id' => $member->id ?? null,
                    'social_media_name' => $social['social_media_name'],
                    'social_link' => $social['social_link'],
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
