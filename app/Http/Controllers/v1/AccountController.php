<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\v1\Controller;
use App\Http\Responses\GenericResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Self-service profile/password for any authenticated role (Secretary,
 * Speaker, and anyone else without their own dedicated profile screens).
 * Always acts on the logged-in user's own row - no role restriction needed.
 */
class AccountController extends Controller
{
    public function profile(Request $request)
    {
        return GenericResponse::success('Profile fetched successfully.', $this->profileCard($request->user()));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'title'      => 'nullable|string|max:255',
            'about'      => 'nullable|string',
            'bio'        => 'nullable|string',
            'phone'      => 'nullable|string|max:50',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:120',
            'postcode'   => 'nullable|string|max:20',
            'county'     => 'nullable|string|max:120',
            'country'    => 'nullable|string|max:120',
        ]);

        if (array_key_exists('city', $data)) {
            $data['ciy'] = $data['city'];
            unset($data['city']);
        }

        $user = $request->user();
        $user->update($data + ['updated_by' => $user->id]);

        return GenericResponse::success('Profile updated successfully.', $this->profileCard($user->fresh()));
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return GenericResponse::error('The current password is incorrect.', 422);
        }

        $user->password = Hash::make($data['new_password']);
        $user->updated_by = $user->id;
        $user->save();

        return GenericResponse::success('Password updated successfully.', null);
    }

    private function profileCard(User $user): array
    {
        return [
            'id'                => $user->id,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'title'             => $user->title,
            'about'             => $user->about,
            'bio'               => $user->bio,
            'email'             => $user->email,
            'phone'             => $user->phone,
            'address'           => $user->address,
            'city'              => $user->ciy,
            'postcode'          => $user->postcode,
            'county'            => $user->county,
            'country'           => $user->country,
            'profile_image_url' => $user->profile_image_url,
        ];
    }
}
