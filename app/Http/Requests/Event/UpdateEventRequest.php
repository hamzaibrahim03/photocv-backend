<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:100',
            'event_date' => 'sometimes|required|date',
            'description' => 'nullable|string',
            'event_type_id' => 'sometimes|required|integer|min:1',
            'event_kind_id' => 'sometimes|required|integer|min:1',
            'duration' => 'sometimes|required|string|max:20',
            'speaker' => 'sometimes|required|string|max:25',
            'speaker_club' => 'sometimes|required|string|max:50',
            'speaker_qualification' => 'sometimes|required|string|max:50',
            'status' => 'sometimes|required|in:scheduled,postpond,tbc,cancelled,completed',
            'required_gear' => 'nullable|string',
            'tags_keywords' => 'nullable|string',
            'url' => 'nullable|url|max:250',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
