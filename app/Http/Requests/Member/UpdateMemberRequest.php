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
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $this->route('user'), // Ensures the email is unique except for the current user
            'username' => 'nullable|string|max:255|unique:users,username,' . $this->route('user'), // Ensures username is unique except for the current user
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validates image upload
            'role' => 'nullable|string|in:member,admin', // Validating role, add more roles if needed
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'profile_image' => 'Profile Image',
            'role' => 'Role',
            'username' => 'Username',
        ];
    }
}
