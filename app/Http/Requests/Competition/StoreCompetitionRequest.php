<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionRequest extends FormRequest
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
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'competition_type_id' => 'nullable|integer|min:1',
            'judging_type_id' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'submission_deadline' => 'nullable|date|after_or_equal:start_date',
            'max_entries_print' => 'nullable|integer|min:0',
            'max_entries_digital' => 'nullable|integer|min:0',
            'allowed_image_formats' => 'nullable|string|max:250',
            'max_file_size' => 'nullable|integer|min:1|max:200',
            'status' => 'nullable|in:scheduled,postpond,draft,cancelled,completed',
            'category_id' => 'nullable|integer|min:1',
            'theme_id' => 'nullable|integer|min:1',
            'print_vs_digital' => 'nullable|in:print,digital',
            'color_vs_mono' => 'nullable|in:color,monochrome',
            'judging_panel' => 'nullable|string',
            'voting_method_id' => 'nullable|integer|min:1',
            'result_method_id' => 'nullable|integer|min:1',
            'result_announcement_date' => 'nullable|date|after_or_equal:submission_deadline',
            'top_places' => 'nullable|string|max:20',
            'high_commendation_number' => 'nullable|integer|min:0',
            'commendation_number' => 'nullable|integer|min:0',
            'prizes' => 'nullable|string',

            // Boolean flags
            'cc_allowed' => 'nullable|boolean',
            'auto_certificate' => 'nullable|boolean',
            'allow_judges_feedback' => 'nullable|boolean',
            'photographer_name' => 'nullable|boolean',
            'comments' => 'nullable|boolean',
            'scores' => 'nullable|boolean',
            'position' => 'nullable|boolean',
            'reels' => 'nullable|boolean',
            'arrows' => 'nullable|boolean',
            'comments_and_critique' => 'nullable|boolean',
            'auto_generate_certificates' => 'nullable|boolean',
            'visible_judges_feedback' => 'nullable|boolean',

            // Judges array (user IDs must exist)
            'judges' => 'nullable|array',
            'judges.*' => 'integer|exists:users,id',

            // Audit fields
            'created_by' => 'nullable|integer|min:1',
            'updated_by' => 'nullable|integer|min:1',
            'deleted_by' => 'nullable|integer|min:1',
        ];
    }


}
