<?php

namespace App\Http\Requests\ClubNews;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubNewsRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'news_type_id' => 'required',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'thumb_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'publish_date' => 'required|date',
        ];
    }
}
