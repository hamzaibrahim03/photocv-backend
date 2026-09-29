<?php

namespace App\Http\Requests\CheatSheet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheatSheetRequest extends FormRequest
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
            'category_id'     => [
                'nullable',
                'integer',
                Rule::exists('catalog', 'id')->where('catalog_type', 'cheat_sheet_category'),
            ],
            'title'           => ($this->isMethod('POST') ? 'required' : 'sometimes|required') . '|string|max:255',
            'description'     => 'nullable|string',
            'reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',

            // Manual settings
            'shutter_min'  => 'nullable|string|max:20',
            'shutter_max'  => 'nullable|string|max:20',
            'iso_min'      => 'nullable|integer|min:0',
            'iso_max'      => 'nullable|integer|min:0|gte:iso_min',
            'aperture_min' => 'nullable|numeric|min:0',
            'aperture_max' => 'nullable|numeric|min:0|gte:aperture_min',

            // Essential settings
            'lens'            => 'nullable|string',
            'camera'          => 'nullable|string',
            'white_balance'   => 'nullable|string',
            'noise_reduction' => 'nullable|string',

            'notes'         => 'nullable|string',
            'accessories'   => 'nullable|array',
            'accessories.*' => 'string|max:255',
            'tags'          => 'nullable|array',
            'tags.*'        => 'string|max:100',
            'visibility'    => 'nullable|in:public,private',
        ];
    }
}
