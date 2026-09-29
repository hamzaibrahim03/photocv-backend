<?php

namespace App\Http\Requests\Gear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GearLibraryRequest extends FormRequest
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
     * gear_ids holds the chosen camera, lenses and accessories (all must be the member's own gear).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ($this->isMethod('POST') ? 'required' : 'sometimes|required') . '|string|max:255',
            'description' => 'nullable|string',
            'gear_ids'    => ($this->isMethod('POST') ? 'required' : 'sometimes') . '|array|min:1',
            'gear_ids.*'  => [
                'integer',
                'distinct',
                Rule::exists('gears', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }
}
