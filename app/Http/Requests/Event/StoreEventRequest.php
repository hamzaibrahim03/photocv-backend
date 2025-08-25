<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'event_date' => 'required|date',
            'description' => 'nullable|string',
            'event_type_id' => 'required|integer|min:1',
            'event_tag_id' => 'required|integer|min:1',
            'duration' => 'required|string|max:20',
            'speaker' => 'required|string|max:25',
            'speaker_club' => 'required|string|max:50',
            'speaker_qualification' => 'required|string|max:50',
            'status' => 'required|in:scheduled,postpond,tbc,cancelled,completed',
            'required_gear' => 'required|string',
            'tags_keywords' => 'required|string',
            'url' => 'required|url|max:250',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
