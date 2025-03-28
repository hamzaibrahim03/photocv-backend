<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubSetting extends Model
{
    use HasFactory;

    protected $table = 'club_settings';

    protected $fillable = [
        'club_name', 'club_tagline', 'about', 'contact_details', 'domain_type', 'domain_name',
        'timezone', 'date', 'club_privacy', 'typography_fonts', 'specific_colors',
        'header_customization', 'footer_customization', 'logo', 'club_banner',
        'registration_access_control', 'members_directory_visibility', 'comments_enabled',
        'likes_enabled', 'website_sections', 'homepage_content_blocks', 'reminders',
        'facebook_link', 'instagram_link', 'flickr_link', 'gdpr_privacy_policy_management',
        'cookies', 'cookies_description', 'data_collection_preferences',
        'data_collection_preferences_description', 'allow_reporting', 'allow_reporting_description'
    ];
}
