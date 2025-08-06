<?php
namespace App\Http\Requests\ClubSettings;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'club_name' => 'required|string|max:255',
            'tag_line' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'contact_details' => 'nullable|string|max:255',
            'domain_type' => 'required|in:custom,subdomain',
            'domain_name' => 'nullable|string|max:255',

            'timezone' => 'required|string',
            'date' => 'required|date',
            'club_privacy' => 'required|in:Public,Members Only,Private',

            'theme_colors' => 'nullable|string',
            'text_color' => 'nullable|string|max:10',
            'primary_color' => 'nullable|string|max:10',
            'background_color' => 'nullable|string|max:10',
            'secondary_color' => 'nullable|string|max:10',
            'accent_color' => 'nullable|string|max:10',

            'typography' => 'nullable|string',
            'fonts' => 'nullable|string',

            'header_title' => 'nullable|string',
            'header_description' => 'nullable|string',
            'footer_text' => 'nullable|string',

            'logo' => 'nullable|image|max:2048',
            'club_banner' => 'nullable|image|max:2048',
            'footer_img' => 'nullable|image|max:2048',
            'header_img' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',

            'registration' => 'required|in:open,invite,manual_approval',
            'directory_visibility' => 'required|in:visible,club_only',
            'comments' => 'required|in:enabled,disabled',
            'likes' => 'required|in:enabled,disabled',

            'website_sections' => 'nullable|in:News,Events,Galleries,Competitions',
            'comment_preference' => 'required|in:All,Fewer,Fewest',
            'reminders' => 'required|in:all,some,none',

            'fb_link_option' => 'boolean',
            'fb_link' => 'nullable|url',
            'insta_link_option' => 'boolean',
            'insta_link' => 'nullable|url',
            'flicker_link_option' => 'boolean',
            'flickr_link' => 'nullable|url',

            'gdpr_privacy_policy_management' => 'nullable|string',
            'cookies' => 'boolean',
            'cookies_description' => 'nullable|string',
            'data_collection_preferences' => 'boolean',
            'data_collection_preferences_description' => 'nullable|string',
            'allow_reporting' => 'boolean',
            'allow_reporting_description' => 'nullable|string',
        ];
    }

}
