<?php

namespace App\Http\Requests\Notice;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
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
    public function rules()
    {
        return [
            'member_id'           => 'nullable|integer|exists:members,id',
            'notice_type_id'      => 'nullable|integer|exists:catalog,id',
            'title'               => 'nullable|string|max:250',
            'description'         => 'nullable|string',
            'tags'                => 'nullable|string|max:250',
            'link_page_url'       => 'nullable|url|max:250',
            'location'            => 'nullable|string|max:255',
            'status'              => 'nullable|in:active,draft,scheduled,deleted,pending',
            'poll'                => 'nullable|in:none,anonymous,public',
            'urgency_importance'  => 'nullable|in:general,urgent,important',
            'comment_allowed'     => 'nullable|in:none,anonymous,public',
            'is_active'           => 'nullable|boolean',
            'images'              => 'nullable|array', // Validate as an array
            'images.*'            => 'file|mimes:jpeg,png,jpg,gif|max:2048', // Validate each file
            'documents'           => 'nullable|array',
            'documents.*'         => 'file|mimes:pdf,doc,docx|max:5120', // Validate each document
        ];
    }

}
