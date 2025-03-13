<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class SignUpRequest extends FormRequest
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
            'username' => 'required|string|unique:users,username|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required',
            'role'     => 'required|string|in:club_admin,member',
        ];
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'Username is required.',
            'email.unique'      => 'The email has already been taken.',
            'username.unique'   => 'The username has already been taken.',
            'email.required'    => 'The email is required',
            'role.required'     => 'Role is required',
            'role.in'           => 'Invalid role provided. Choose either "club_admin" or "member".',
        ];
    }
}
