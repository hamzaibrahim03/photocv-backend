<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionRequest extends FormRequest
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
        $route = $this->route()->getActionMethod();

        // Define validation rules based on the route
        if ($route === 'joinCompetition') {
            return [
                'comp_id' => 'required|exists:competitions,id',
            ];
        }

        if( $route === 'submitCompetitionEntry' ) {
            return [
                'member_comp_id' => 'required|exists:competition_members,id',
                'entry_type' => 'required|in:print,digital',
                'entry_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
        }

        return [];
    }
}
