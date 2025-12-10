<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:50',
            'catalog_type' => 'sometimes|required|string|in:competition_type,event_type,event_tag,judging_type,competition_theme,competition_catagories,competition_voting_method,competition_result_method,max_image_per_gallery,max_image_file_size,max_image_width,max_image_height,news_type,notice_type',
        ];
    }
}
