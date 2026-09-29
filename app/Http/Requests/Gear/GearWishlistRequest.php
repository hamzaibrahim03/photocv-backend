<?php

namespace App\Http\Requests\Gear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GearWishlistRequest extends FormRequest
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
            'list_type'    => 'sometimes|in:wish,gift',
            'title'        => ($this->isMethod('POST') ? 'required' : 'sometimes|required') . '|string|max:255',
            'category_id'  => [
                'nullable',
                'integer',
                Rule::exists('catalog', 'id')->where('catalog_type', 'gear_category'),
            ],
            'price'        => 'nullable|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link'         => 'nullable|url|max:500',
            'is_purchased' => 'nullable|boolean',
        ];
    }
}
