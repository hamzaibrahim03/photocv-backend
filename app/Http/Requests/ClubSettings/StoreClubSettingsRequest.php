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
            'club_tagline' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'contact_details' => 'nullable|string',
            'domain_type' => 'required|in:Custom,Subdomain',
            'domain_name' => 'nullable|string|max:255',
            'timezone' => 'required|string',
            'date' => 'required|date',
            'club_privacy' => 'required|in:Public,Members Only,Private',
            'typography_fonts' => 'nullable|string',
            'specific_colors' => 'nullable|string',
            'header_customization' => 'nullable|string',
            'footer_customization' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'club_banner' => 'nullable|image|max:2048',
            'registration_access_control' => 'required|in:Open,Invite Only,Manual Approval',
            'members_directory_visibility' => 'required|in:Visible,Club Only',
            'comments_enabled' => 'boolean',
            'likes_enabled' => 'boolean',
            'website_sections' => 'nullable|in:Enable/Disable News,Events,Galleries,Competitions',
            'homepage_content_blocks' => 'required|in:All,Fewer,Fewest',
            'reminders' => 'required|in:All,Some,None',
            'facebook_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
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
