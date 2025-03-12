<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:15',
            'email' => 'required|email|unique:schools,email,' . $this->id,
            'address' => 'required|string|max:500',
        ];
    }

    public function message()
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than 255 characters.',

            'mobile_no.required' => 'The mobile number is required.',
            'mobile_no.string' => 'The mobile number must be a valid string.',
            'mobile_no.max' => 'The mobile number may not be greater than 15 characters.',

            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',

            'address.required' => 'The address is required.',
            'address.string' => 'The address must be a valid string.',
            'address.max' => 'The address may not be greater than 500 characters.',
        ];
    }

}
