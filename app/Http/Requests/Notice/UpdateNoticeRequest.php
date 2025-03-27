<?php

namespace App\Http\Requests\Notice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoticeRequest extends FormRequest
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
            'member_id'          => 'nullable|integer|exists:members,id',
            'notice_type_id'     => 'nullable|integer|exists:catalog,id',
            'title'              => 'nullable|string|max:250',
            'description'        => 'nullable|string',
            'tags'               => 'nullable|string|max:250',
            'link_page_url'      => 'nullable|url|max:250',
            'location'           => 'nullable|string|max:255',
            'status'             => 'nullable|in:active,draft,scheduled,deleted,pending',
            'poll'               => 'nullable|in:none,anonymous,public',
            'urgency_importance' => 'nullable|in:general,urgent,important',
            'comment_allowed'    => 'nullable|in:none,anonymous,public',
            'is_active'          => 'nullable|boolean',
            'images'             => 'nullable|array',
            'images.*'           => 'file|mimes:jpg,jpeg,png,gif|max:2048', // Max 2MB per image
            'documents'          => 'nullable|array',
            'documents.*'        => 'file|mimes:pdf,doc,docx|max:5120', // Max 5MB per document
        ];
    }

    public function messages()
    {
        return [
            'images.*.mimes'    => 'Only JPG, JPEG, PNG, and GIF images are allowed.',
            'images.*.max'      => 'Each image must be less than 2MB.',
            'documents.*.mimes' => 'Only PDF, DOC, and DOCX files are allowed.',
            'documents.*.max'   => 'Each document must be less than 5MB.',
        ];
    }
}
