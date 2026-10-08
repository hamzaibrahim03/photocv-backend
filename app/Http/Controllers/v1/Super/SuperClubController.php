<?php

namespace App\Http\Controllers\v1\Super;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\Club;
use App\Models\ClubSetting;
use App\Models\Gallery;
use App\Models\MemberClub;
use Illuminate\Http\Request;

class SuperClubController extends Controller
{
    public function index(Request $request)
    {
        $query = Club::with(['setting', 'user'])->withCount('users');

        if ($request->filled('search')) {
            $query->where('club_name', 'like', '%' . $request->query('search') . '%');
        }

        $clubs = $query->orderByDesc('created_at')->get()->map(fn ($club) => $this->formatClub($club));

        return MemberResponse::success('Clubs retrieved successfully.', $clubs);
    }

    public function show(string $id)
    {
        $club = Club::with(['setting', 'user'])->withCount('users')->findOrFail($id);
        return MemberResponse::success('Club retrieved successfully.', $this->formatClub($club));
    }

    public function store(Request $request)
    {
        $club = Club::create($request->only([
            'club_name', 'tag_line', 'about', 'contact_details',
            'domain_type', 'domain_name', 'established_year',
        ]));
        $this->syncSettings($club, $request);

        return MemberResponse::success('Club created successfully.', $this->formatClub($club->fresh(['setting', 'user'])), 201);
    }

    public function update(Request $request, string $id)
    {
        $club = Club::findOrFail($id);
        $club->update($request->only([
            'club_name', 'tag_line', 'about', 'contact_details',
            'domain_type', 'domain_name', 'established_year',
        ]));
        $this->syncSettings($club, $request);

        return MemberResponse::success('Club updated successfully.', $this->formatClub($club->fresh(['setting', 'user'])));
    }

    public function destroy(string $id)
    {
        Club::findOrFail($id)->delete();
        return MemberResponse::success('Club deleted successfully.', null);
    }

    public function toggleStatus(string $id)
    {
        $club = Club::with('user')->findOrFail($id);
        if ($club->user) {
            $club->user->account_status = $club->user->account_status === 'active' ? 'inactive' : 'active';
            $club->user->save();
        }

        return MemberResponse::success('Club status updated.', $this->formatClub($club->fresh(['setting', 'user'])));
    }

    /**
     * Platform-wide committee/member listing for one club (role + image count).
     */
    public function members(string $id)
    {
        $club = Club::with(['users.roles', 'setting'])->findOrFail($id);

        $joinYearByMemberId = MemberClub::where('club_id', $club->id)
            ->get()
            ->keyBy('member_id')
            ->map(fn ($mc) => $mc->joining_date?->format('Y'));

        $templatePreview = $club->setting?->template_preview_url;
        $website = $club->domain_name ? "http://{$club->domain_name}" : null;

        $members = $club->users->map(function ($user) use ($joinYearByMemberId, $templatePreview, $website) {
            $imageCount = Gallery::where('member_id', $user->id)->withCount('photos')->get()->sum('photos_count');

            return [
                'id' => $user->id,
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
                'role' => $user->roles->first()->name ?? 'General Members',
                'images' => $imageCount,
                'status' => $user->account_status !== 'inactive',
                'avatar' => $user->profile_image_url,
                'templatePreview' => $templatePreview,
                'establishedYear' => $joinYearByMemberId->get($user->id),
                'website' => $website,
            ];
        });

        return MemberResponse::success('Club members retrieved successfully.', $members);
    }

    private function formatClub(Club $club): array
    {
        return [
            'id' => $club->id,
            'name' => $club->club_name,
            'tagline' => $club->tag_line,
            'about' => $club->about,
            'established' => $club->established_year,
            'domain_type' => $club->domain_type,
            'domain_name' => $club->domain_name,
            'status' => ($club->user?->account_status ?? 'active') === 'inactive' ? 'inactive' : 'active',
            'membersCount' => $club->users_count ?? 0,
            'logo' => $club->setting?->logo_url,
            'cover' => $club->setting?->club_banner_url,
        ];
    }

    private function syncSettings(Club $club, Request $request): void
    {
        $settingData = [];

        if ($request->hasFile('logo')) {
            $settingData['logo'] = $request->file('logo')->store('uploads/clubs/logo', 'public');
        }
        if ($request->hasFile('cover')) {
            $settingData['club_banner'] = $request->file('cover')->store('uploads/clubs/banner', 'public');
        }
        if (empty($settingData)) {
            return;
        }

        $setting = $club->setting;
        if ($setting) {
            $setting->update($settingData);
            return;
        }

        $settingData['club_id'] = $club->id;
        $settingData['club_privacy'] = 'Public';
        $settingData['website_sections'] = 'News';
        $settingData['comment_preference'] = 'All';
        ClubSetting::create($settingData);
    }
}
