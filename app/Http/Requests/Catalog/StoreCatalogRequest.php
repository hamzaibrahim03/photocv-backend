<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogRequest extends FormRequest
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
            'name' => 'required|string|max:50',
            'catalog_type' => 'required|string|in:competition_type,event_type,event_tag,judging_type,competition_theme,competition_catagories,competition_voting_method,competition_result_method',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ];
    }
}
