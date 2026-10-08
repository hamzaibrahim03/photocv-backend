<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'show_recent_profiles',
        'show_top_profiles',
        'show_featured_profiles',
        'show_recent_clubs',
        'show_top_clubs',
        'show_featured_clubs',
        'show_contact_section',
        'show_social_links',
        'allow_club_signup',
        'allow_photographer_signup',
        'require_approval',
        'custom_signup_enabled',
        'custom_signup_message',
        'cookie_consent_enabled',
        'cookie_message',
        'privacy_policy',
        'terms_conditions',
    ];

    protected $casts = [
        'show_recent_profiles' => 'boolean',
        'show_top_profiles' => 'boolean',
        'show_featured_profiles' => 'boolean',
        'show_recent_clubs' => 'boolean',
        'show_top_clubs' => 'boolean',
        'show_featured_clubs' => 'boolean',
        'show_contact_section' => 'boolean',
        'show_social_links' => 'boolean',
        'allow_club_signup' => 'boolean',
        'allow_photographer_signup' => 'boolean',
        'require_approval' => 'boolean',
        'custom_signup_enabled' => 'boolean',
        'cookie_consent_enabled' => 'boolean',
    ];
}
