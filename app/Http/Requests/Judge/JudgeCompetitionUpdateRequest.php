<?php

namespace App\Http\Requests\Judge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JudgeCompetitionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Assignment to the competition is checked in the repository.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * "Basic Information" fields a judge may edit.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                => 'sometimes|required|string|max:255',
            'competition_type_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('catalog', 'id')->where('catalog_type', 'competition_type'),
            ],
            'description'         => 'sometimes|nullable|string',
            'status'              => 'sometimes|required|in:postponed,scheduled,cancelled,completed,draft',
        ];
    }
}
