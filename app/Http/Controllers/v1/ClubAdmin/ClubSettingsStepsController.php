<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\ClubSetting;
use Illuminate\Http\Request;

/**
 * Backs the 5-step "Settings" wizard on ClubRoute.jsx (General / Appearance /
 * Membership / Content / Privacy). Each pair of endpoints reads and writes
 * the same club + club_settings rows the rest of the app already uses, so
 * Super admin's club management and this wizard always agree.
 */
class ClubSettingsStepsController extends Controller
{
    private function club(Request $request)
    {
        return $request->user()->club;
    }

    private function setting(Request $request): ClubSetting
    {
        $club = $this->club($request);
        $setting = $club->setting;
        if (!$setting) {
            $setting = ClubSetting::create(['club_id' => $club->id]);
        }
        return $setting;
    }

    public function generalShow(Request $request)
    {
        $club = $this->club($request);
        $setting = $club->setting;

        return MemberResponse::success('General settings retrieved.', [
            'name' => $club->club_name,
            'tagline' => $club->tag_line,
            'about' => $club->about,
            'contact_details' => $club->contact_details,
            'domain_type' => $club->domain_type,
            'domain_name' => $club->domain_name,
            'timezone_offset' => $setting?->timezone,
            'privacy' => $setting?->club_privacy,
        ]);
    }

    public function generalStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:250',
            'tagline' => 'nullable|string|max:250',
            'about' => 'nullable|string',
            'contact_details' => 'nullable|string',
            'domain_type' => 'nullable|string',
            'domain_name' => 'nullable|string|max:250',
            'timezone_offset' => 'nullable|string',
            'privacy' => 'nullable|string',
        ]);

        $club = $this->club($request);
        $club->update([
            'club_name' => $data['name'],
            'tag_line' => $data['tagline'] ?? null,
            'about' => $data['about'] ?? null,
            'contact_details' => $data['contact_details'] ?? null,
            'domain_type' => $data['domain_type'] ?? null,
            'domain_name' => $data['domain_name'] ?? null,
        ]);

        $setting = $this->setting($request);
        $setting->update([
            'timezone' => $data['timezone_offset'] ?? $setting->timezone,
            'club_privacy' => $data['privacy'] ?? $setting->club_privacy,
        ]);

        return MemberResponse::success('General settings saved successfully.', null);
    }

    public function appearanceShow(Request $request)
    {
        $setting = $this->club($request)->setting;

        return MemberResponse::success('Appearance settings retrieved.', [
            'header_title' => $setting?->header_title,
            'footer_title' => $setting?->footer_text,
            'header_description' => $setting?->header_description,
            'footer_description' => $setting?->footer_description,
            'logos_url' => $setting?->logo_url,
            'banner_url' => $setting?->club_banner_url,
            'favicon_url' => $setting?->favicon_url,
        ]);
    }

    public function appearanceStore(Request $request)
    {
        $data = $request->validate([
            'header_title' => 'nullable|string|max:255',
            'footer_title' => 'nullable|string|max:255',
            'header_description' => 'nullable|string',
            'footer_description' => 'nullable|string',
            'logos' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:5120',
            'favicon' => 'nullable|image|max:2048',
        ]);

        $setting = $this->setting($request);
        $payload = [
            'header_title' => $data['header_title'] ?? $setting->header_title,
            'footer_text' => $data['footer_title'] ?? $setting->footer_text,
            'header_description' => $data['header_description'] ?? $setting->header_description,
            'footer_description' => $data['footer_description'] ?? $setting->footer_description,
        ];

        if ($request->hasFile('logos')) {
            $payload['logo'] = $request->file('logos')->store('club_logos', 'public');
        }
        if ($request->hasFile('banner')) {
            $payload['club_banner'] = $request->file('banner')->store('club_banners', 'public');
        }
        if ($request->hasFile('favicon')) {
            $payload['favicon'] = $request->file('favicon')->store('club_favicons', 'public');
        }

        $setting->update($payload);

        return MemberResponse::success('Appearance settings saved successfully.', null);
    }

    /**
     * The frontend's radio-button labels ("Invite Only", "Enable", ...) are
     * Title Case for display; the DB enums are lowercase/snake_case. These
     * maps translate both directions.
     */
    private const REGISTRATION_TO_DB = ['Open' => 'open', 'Invite Only' => 'invite', 'Manual Approval' => 'manual_approval'];
    private const REGISTRATION_TO_UI = ['open' => 'Open', 'invite' => 'Invite Only', 'manual_approval' => 'Manual Approval'];
    private const VISIBILITY_TO_DB = ['Visible' => 'visible', 'Club Only' => 'club_only'];
    private const VISIBILITY_TO_UI = ['visible' => 'Visible', 'club_only' => 'Club Only'];
    private const ENABLED_TO_DB = ['Enable' => 'enabled', 'Disable' => 'disabled'];
    private const ENABLED_TO_UI = ['enabled' => 'Enable', 'disabled' => 'Disable'];
    private const REMINDER_TO_DB = ['All' => 'all', 'Some' => 'some', 'None' => 'none'];
    private const REMINDER_TO_UI = ['all' => 'All', 'some' => 'Some', 'none' => 'None'];

    public function membershipShow(Request $request)
    {
        $setting = $this->club($request)->setting;

        return MemberResponse::success('Membership settings retrieved.', [
            'registration' => self::REGISTRATION_TO_UI[$setting?->registration] ?? null,
            'directory_visibility' => self::VISIBILITY_TO_UI[$setting?->directory_visibility] ?? null,
            'comments' => self::ENABLED_TO_UI[$setting?->comments] ?? null,
            'likes' => self::ENABLED_TO_UI[$setting?->likes] ?? null,
        ]);
    }

    public function membershipStore(Request $request)
    {
        $data = $request->validate([
            'registration' => 'nullable|string',
            'directory_visibility' => 'nullable|string',
            'comments' => 'nullable|string',
            'likes' => 'nullable|string',
        ]);

        $setting = $this->setting($request);
        $setting->update([
            'registration' => isset($data['registration']) ? (self::REGISTRATION_TO_DB[$data['registration']] ?? $setting->registration) : $setting->registration,
            'directory_visibility' => isset($data['directory_visibility']) ? (self::VISIBILITY_TO_DB[$data['directory_visibility']] ?? $setting->directory_visibility) : $setting->directory_visibility,
            'comments' => isset($data['comments']) ? (self::ENABLED_TO_DB[$data['comments']] ?? $setting->comments) : $setting->comments,
            'likes' => isset($data['likes']) ? (self::ENABLED_TO_DB[$data['likes']] ?? $setting->likes) : $setting->likes,
        ]);

        return MemberResponse::success('Membership settings saved successfully.', null);
    }

    public function contentShow(Request $request)
    {
        $setting = $this->club($request)->setting;

        return MemberResponse::success('Content settings retrieved.', [
            'sections' => $setting?->website_sections ? explode(',', $setting->website_sections) : [],
            'homepage' => $setting?->homepage_content_blocks,
            'reminder' => self::REMINDER_TO_UI[$setting?->reminders] ?? null,
            'email' => $setting?->contact_email,
            'phone' => $setting?->contact_phone,
            'address' => $setting?->contact_address,
            'facebook_enabled' => (bool) $setting?->fb_link_option,
            'facebook_url' => $setting?->fb_link,
            'instagram_enabled' => (bool) $setting?->insta_link_option,
            'instagram_url' => $setting?->insta_link,
            'flickr_enabled' => (bool) $setting?->flickr_link_option,
            'flickr_url' => $setting?->flickr_link,
        ]);
    }

    public function contentStore(Request $request)
    {
        $data = $request->validate([
            'sections' => 'nullable|array',
            'sections.*' => 'string',
            'homepage' => 'nullable|string',
            'reminder' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'facebook_enabled' => 'nullable|boolean',
            'facebook_url' => 'nullable|string|max:255',
            'instagram_enabled' => 'nullable|boolean',
            'instagram_url' => 'nullable|string|max:255',
            'flickr_enabled' => 'nullable|boolean',
            'flickr_url' => 'nullable|string|max:255',
        ]);

        $this->setting($request)->update([
            'website_sections' => isset($data['sections']) ? implode(',', $data['sections']) : null,
            'homepage_content_blocks' => $data['homepage'] ?? null,
            'reminders' => self::REMINDER_TO_DB[$data['reminder'] ?? ''] ?? null,
            'contact_email' => $data['email'] ?? null,
            'contact_phone' => $data['phone'] ?? null,
            'contact_address' => $data['address'] ?? null,
            'fb_link_option' => (bool) ($data['facebook_enabled'] ?? false),
            'fb_link' => $data['facebook_url'] ?? null,
            'insta_link_option' => (bool) ($data['instagram_enabled'] ?? false),
            'insta_link' => $data['instagram_url'] ?? null,
            'flickr_link_option' => (bool) ($data['flickr_enabled'] ?? false),
            'flickr_link' => $data['flickr_url'] ?? null,
        ]);

        return MemberResponse::success('Content settings saved successfully.', null);
    }

    public function privacyShow(Request $request)
    {
        $setting = $this->club($request)->setting;

        return MemberResponse::success('Privacy settings retrieved.', [
            'privacy_policy_url' => $setting?->gdpr_privacy_policy_management,
            'cookies_enabled' => (bool) $setting?->cookies,
            'cookies_notes' => $setting?->cookies_description,
            'data_collection_enabled' => (bool) $setting?->data_collection_preferences,
            'data_collection_notes' => $setting?->data_collection_preferences_description,
            'allow_reporting' => (bool) $setting?->allow_reporting,
            'reporting_notes' => $setting?->allow_reporting_description,
        ]);
    }

    public function privacyStore(Request $request)
    {
        $data = $request->validate([
            'privacy_policy_url' => 'nullable|string',
            'cookies_enabled' => 'nullable|boolean',
            'cookies_notes' => 'nullable|string',
            'data_collection_enabled' => 'nullable|boolean',
            'data_collection_notes' => 'nullable|string',
            'allow_reporting' => 'nullable|boolean',
            'reporting_notes' => 'nullable|string',
        ]);

        $this->setting($request)->update([
            'gdpr_privacy_policy_management' => $data['privacy_policy_url'] ?? null,
            'cookies' => (bool) ($data['cookies_enabled'] ?? false),
            'cookies_description' => $data['cookies_notes'] ?? null,
            'data_collection_preferences' => (bool) ($data['data_collection_enabled'] ?? false),
            'data_collection_preferences_description' => $data['data_collection_notes'] ?? null,
            'allow_reporting' => (bool) ($data['allow_reporting'] ?? false),
            'allow_reporting_description' => $data['reporting_notes'] ?? null,
        ]);

        return MemberResponse::success('Privacy settings saved successfully.', null);
    }
}
