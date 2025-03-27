<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
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
            'title'         => 'required|string|max:255',
            'publish_date'  => 'required|date_format:Y-m-d',
            'description'   => 'required|string',
            'thumb_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'page_type_id'  => 'required|exists:catalog,id',
        ];
    }
}
