<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
        if ($route === 'postCommentOrLikes') {
            return [
                'record_id'     => 'required|integer',
                'record_type'   => 'required|in:page,news,notice,event,photo,competition_entry',
                'comment_type'   => 'required|in:comment,liking',
                'comment'       => 'nullable|string',
                'interacted_by' => 'nullable|integer|exists:users,id',
                'is_published'  => 'nullable|boolean',
                'admin_notes'   => 'nullable|string|max:250',
            ];
        }

        if ($route === 'update') {
            return [
                'title'         => 'required|string|max:255',
                'full_name'    => 'sometimes|string|max:255',
                'email'        => 'sometimes|email|unique:users,email,' . $this->route('id'),
                'about'        => 'sometimes|string',
                'phone'        => 'sometimes|string|max:20',
                'profile_image'=> 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'role_id'      => 'sometimes|exists:roles,id',

                // social media validation
                'member_social_media'                     => 'nullable|array',
                'member_social_media.*.social_media_name' => 'required_with:member_social_media|string|max:100',
                'member_social_media.*.social_link'       => 'required_with:member_social_media|url|max:255',
            ];
        }

        return [
            'title'    => 'required|string|max:255',
            'full_name'    => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'about'         => 'nullable|string',
            'phone'         => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role_id'       => 'required',
            
            // social media validation
            'member_social_media'                     => 'nullable|array',
            'member_social_media.*.social_media_name' => 'required_with:member_social_media|string|max:100',
            'member_social_media.*.social_link'       => 'required_with:member_social_media|url|max:255',
        ];
    }
}
