<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionGlobalSettingRequest extends FormRequest
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
            'competition_type_id' => 'nullable|integer',
            'judging_type_id'     => 'nullable|integer',
            'status'              => 'nullable|in:scheduled,postpond,draft,cancelled,completed',
            'max_entries_print'   => 'nullable|integer|min:0',
            'max_entries_digital' => 'nullable|integer|min:0',
            'allowed_image_formats' => 'nullable|string|max:250',
            'max_file_size'       => 'nullable|integer|min:1',
            'category_id'         => 'nullable|integer',
            'theme_id'            => 'nullable|integer',
            'print_vs_digital'    => 'nullable|in:print,digital',
            'color_vs_mono'       => 'nullable|in:color,monochrome',
            'voting_method_id'    => 'nullable|integer',
            'top_places'          => 'nullable|string|max:20',
            'high_commendation_number' => 'nullable|integer|min:0',
            'commendation_number' => 'nullable|integer|min:0',
            'points_first_place'  => 'nullable|integer|min:0',
            'points_second_place' => 'nullable|integer|min:0',
            'points_third_place'  => 'nullable|integer|min:0',
            'points_high_commendation' => 'nullable|integer|min:0',
            'points_commendation' => 'nullable|integer|min:0',
            'comments_and_critique' => 'boolean',
            'auto_generate_certificates' => 'boolean',
            'visible_judges_feedback'    => 'boolean',
        ];
    }
}
