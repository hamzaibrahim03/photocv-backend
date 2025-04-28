<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\UtilityTrait;
use App\Http\Responses\MemberResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberCreatedMail;
use Illuminate\Support\Facades\Storage;

class MemberRepository implements MemberRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $members = $this->getAllIndexData($request, User::role('member')->get());
            return MemberResponse::success( 'Members retrieved successfully.', $members );
        } catch (\Exception $e) {
            return MemberResponse::error( $e->getMessage(), $e->getCode() ?: 500 );
        }
    }

    public function show( $id )
    {
        try {
            $member = User::findOrFail($id);
            if (!$member) {
                return MemberResponse::error('Member not found.', 404);
            }

            return MemberResponse::success('Member retrieved successfully.', $member);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(array $data, $file = null)
    {
        try {
            $randomPassword = Str::random(10);
            $data['password'] = bcrypt($randomPassword);
            if ($file) {
                $data['profile_image'] = $file->store('profile_images', 'public');
            }

            $usernameBase = Str::slug($data['first_name'] . $data['last_name'], '');
            $data['username'] = $this->generateUniqueUsername($usernameBase);

            $member = User::create($data);
            $member->assignRole('member');

            // Mail::to($data['email'])->send(new MemberCreatedMail($data['email'], $randomPassword));

            return MemberResponse::success('Member created successfully.', $member, 201);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function update($id, array $data, $file = null)
    {
        try {
            unset($data['password']);
            unset($data['email']);

            $member = User::findOrFail($id);
    
            // Handle profile image update
            if ($file) {
                // Delete old image if exists
                if ($member->profile_image) {
                    Storage::disk('public')->delete($member->profile_image);
                }
                $data['profile_image'] = $file->store('profile_images', 'public');
            }
    
            // Update username only if it's changed and ensure uniqueness
            // if (!empty($data['first_name']) || !empty($data['last_name'])) {
            //     $usernameBase = Str::slug(($data['first_name'] ?? $member->first_name) . ($data['last_name'] ?? $member->last_name), '');
            //     if ($usernameBase !== $member->username) {
            //         $data['username'] = $this->generateUniqueUsername($usernameBase);
            //     }
            // }
    
            // Update member details
            $member->update($data);
    
            return MemberResponse::success('Member updated successfully.', $member);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function delete($id)
    {
        try {
            $member = User::findOrFail($id);

            if (!$member) {
                return MemberResponse::error('Member not found or already deleted.', 404);
            }

            $member->delete();
            return MemberResponse::success('Member deleted successfully.');
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    // Function to ensure unique username
    private function generateUniqueUsername($usernameBase)
    {
        $username = $usernameBase;
        $count = 1;

        while (User::where('username', $username)->exists()) {
            $username = $usernameBase . $count;
            $count++;
        }

        return $username;
    }

    public function memberGalleryImages( $images ) {
        
    }

}
