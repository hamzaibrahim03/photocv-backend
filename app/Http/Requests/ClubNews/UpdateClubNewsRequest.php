<?php

namespace App\Http\Requests\ClubNews;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubNewsRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'news_type_id' => 'sometimes|required|integer',
            'short_description' => 'nullable|string',
            'description' => 'sometimes|required|string',
            'thumb_image' => 'nullable|image|max:2048', // Max 2MB
            'publish_date' => 'sometimes|required|date_format:d-m-Y',
        ];
    }
}
