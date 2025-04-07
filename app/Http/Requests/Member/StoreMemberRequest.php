<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'about'         => 'nullable|string',
            'address'       => 'nullable|string',
            'city'          => 'nullable|string|max:255',
            'postcode'      => 'nullable|string|max:10',
            'country'       => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'bio'           => 'nullable|string',
            'status'        => 'nullable|in:approved,pending,rejected',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
