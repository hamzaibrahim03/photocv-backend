<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6',
            'tag_line' => 'nullable|string',
            'about' => 'nullable|string',
            'name' => 'nullable|string',

            'member.domain_name' => 'nullable|string',
            'member.color_theme' => 'nullable|string',
            'member.cover_image' => 'nullable|string',
            'member.font' => 'nullable|string',
            'member.profile_privacy' => 'nullable|string',
            'member.footer_text' => 'nullable|string',
            'member.social_links_visibility' => 'nullable|array',

            'member_brands.interest' => 'nullable|array',
            'member_brands.brands' => 'nullable|array',

            'member_contact.email' => 'nullable|email',
            'member_contact.phone' => 'nullable|string',
            'member_contact.address' => 'nullable|string',

            'member_social_media' => 'nullable|array',
            'member_social_media.*.social_media_name' => 'required_with:member_social_media|string',
            'member_social_media.*.social_link' => 'required_with:member_social_media|url',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    // public function attributes()
    // {
    //     return [
    //         'first_name' => 'First Name',
    //         'last_name' => 'Last Name',
    //         'email' => 'Email Address',
    //         'profile_image' => 'Profile Image',
    //         'role' => 'Role',
    //         'username' => 'Username',
    //     ];
    // }
}
