<?php

namespace App\Http\Requests\FeatureImage;

use Illuminate\Foundation\Http\FormRequest;

class FeatureImageRequest extends FormRequest
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
            'featured_image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100' // Optional
            ],
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'Please select an image to upload',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Only JPEG, PNG, JPG and GIF images are allowed',
            'image.max' => 'The image may not be greater than 2MB',
            'image.dimensions' => 'The image must be at least 100x100 pixels'
        ];
    }
}
