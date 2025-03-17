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
            'catalog_type' => 'required|string|in:competetion_type,event_type,event_kind,judging_type,competetion_theme,competetion_catagories,competetion_voting_method,competetion_result_method',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ];
    }
}
