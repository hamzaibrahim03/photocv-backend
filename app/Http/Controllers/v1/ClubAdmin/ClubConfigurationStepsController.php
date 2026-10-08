<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\Club;
use App\Models\ClubConfigItem;
use App\Models\ClubSeason;
use App\Models\ClubSetting;
use Illuminate\Http\Request;

/**
 * Backs the 5-step "Configuration" wizard on ClubRoute.jsx (General / News /
 * Events / Galleries / Competitions). The "named list with an optional icon"
 * sections (notice types, news types, event tags, competition categories...)
 * all share one shape, so they're all stored as ClubConfigItem rows grouped
 * by a `group` key rather than one near-identical table per list.
 *
 * Note: the frontend forms submit these lists as a plain JSON POST body
 * (apiClient's default Content-Type), so a per-item `icon` File picked in
 * the browser can never actually serialize into that request - only
 * `name` reliably arrives here. Icon upload would need those forms
 * switched to multipart/FormData to work end-to-end; out of scope for
 * this pass, flagged for follow-up.
 */
class ClubConfigurationStepsController extends Controller
{
    private function club(Request $request): Club
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

    private function listItems(Club $club, string $group): array
    {
        return ClubConfigItem::where('club_id', $club->id)
            ->where('group', $group)
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon_url,
            ])
            ->values()
            ->all();
    }

    /**
     * Replaces every item in the given club+group with the provided list,
     * matching the frontend's "send the whole array back" save semantics.
     */
    private function syncItems(Club $club, string $group, array $items): array
    {
        ClubConfigItem::where('club_id', $club->id)->where('group', $group)->delete();

        foreach ($items as $item) {
            $name = is_array($item) ? ($item['name'] ?? null) : null;
            if (!$name) {
                continue;
            }
            ClubConfigItem::create([
                'club_id' => $club->id,
                'group' => $group,
                'name' => $name,
                'icon' => null,
            ]);
        }

        return $this->listItems($club, $group);
    }

    public function generalShow(Request $request)
    {
        $club = $this->club($request);
        $seasons = ClubSeason::where('club_id', $club->id)->orderBy('id')->get()->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'status' => (bool) $s->status,
            'startDate' => $s->start_date,
            'endDate' => $s->end_date,
        ])->values()->all();

        return MemberResponse::success('General configuration retrieved.', [
            'noticeTypes' => $this->listItems($club, 'notice_type'),
            'pageTypes' => $this->listItems($club, 'page_type'),
            'seasons' => $seasons,
        ]);
    }

    public function generalStore(Request $request)
    {
        $data = $request->validate([
            'noticeTypes' => 'nullable|array',
            'pageTypes' => 'nullable|array',
            'seasons' => 'nullable|array',
            'seasons.*.name' => 'required_with:seasons|string',
            'seasons.*.status' => 'nullable|boolean',
            'seasons.*.startDate' => 'nullable|date',
            'seasons.*.endDate' => 'nullable|date',
        ]);

        $club = $this->club($request);
        $this->syncItems($club, 'notice_type', $data['noticeTypes'] ?? []);
        $this->syncItems($club, 'page_type', $data['pageTypes'] ?? []);

        ClubSeason::where('club_id', $club->id)->delete();
        foreach ($data['seasons'] ?? [] as $season) {
            ClubSeason::create([
                'club_id' => $club->id,
                'name' => $season['name'],
                'status' => $season['status'] ?? true,
                'start_date' => $season['startDate'] ?? null,
                'end_date' => $season['endDate'] ?? null,
            ]);
        }

        return MemberResponse::success('General configuration saved successfully.', null);
    }

    public function newsShow(Request $request)
    {
        $club = $this->club($request);

        return MemberResponse::success('News configuration retrieved.', [
            'newsTypes' => $this->listItems($club, 'news_type'),
        ]);
    }

    public function newsStore(Request $request)
    {
        $data = $request->validate(['newsTypes' => 'nullable|array']);
        $club = $this->club($request);
        $this->syncItems($club, 'news_type', $data['newsTypes'] ?? []);

        return MemberResponse::success('News configuration saved successfully.', null);
    }

    public function eventsShow(Request $request)
    {
        $club = $this->club($request);

        return MemberResponse::success('Event configuration retrieved.', [
            'eventTypes' => $this->listItems($club, 'event_type'),
            'eventTags' => $this->listItems($club, 'event_tag'),
        ]);
    }

    public function eventsStore(Request $request)
    {
        $data = $request->validate([
            'eventTypes' => 'nullable|array',
            'eventTags' => 'nullable|array',
        ]);
        $club = $this->club($request);
        $this->syncItems($club, 'event_type', $data['eventTypes'] ?? []);
        $this->syncItems($club, 'event_tag', $data['eventTags'] ?? []);

        return MemberResponse::success('Event configuration saved successfully.', null);
    }

    public function galleriesShow(Request $request)
    {
        $setting = $this->club($request)->setting;

        return MemberResponse::success('Gallery configuration retrieved.', [
            'max_images_per_gallery' => $setting?->max_images_per_gallery,
            'max_image_file_size' => $setting?->max_image_file_size,
            'max_image_width' => $setting?->max_image_width,
            'max_image_height' => $setting?->max_image_height,
        ]);
    }

    public function galleriesStore(Request $request)
    {
        $data = $request->validate([
            'max_images_per_gallery' => 'nullable|integer|min:0',
            'max_image_file_size' => 'nullable|numeric|min:0',
            'max_image_width' => 'nullable|integer|min:0',
            'max_image_height' => 'nullable|integer|min:0',
        ]);

        $this->setting($request)->update($data);

        return MemberResponse::success('Gallery configuration saved successfully.', null);
    }

    private const COMPETITION_GROUPS = [
        'types' => 'competition_type',
        'themes' => 'competition_theme',
        'categories' => 'competition_category',
        'votingMethods' => 'competition_voting_method',
        'deliveryMethods' => 'competition_delivery_method',
        'resultMethods' => 'competition_result_method',
        'judgingTypes' => 'judging_type',
    ];

    private const COMPETITION_POST_KEYS = [
        'types' => 'competition_types',
        'themes' => 'competition_themes',
        'categories' => 'competition_categories',
        'votingMethods' => 'competition_voting_methods',
        'deliveryMethods' => 'competition_entry_delivery_methods',
        'resultMethods' => 'competition_result_methods',
        'judgingTypes' => 'judging_types',
    ];

    public function competitionsShow(Request $request)
    {
        $club = $this->club($request);
        $response = [];
        foreach (self::COMPETITION_GROUPS as $key => $group) {
            $response[$key] = $this->listItems($club, $group);
        }

        return MemberResponse::success('Competition configuration retrieved.', $response);
    }

    public function competitionsStore(Request $request)
    {
        $club = $this->club($request);
        foreach (self::COMPETITION_GROUPS as $stateKey => $group) {
            $postKey = self::COMPETITION_POST_KEYS[$stateKey];
            $items = $request->input($postKey, []);
            $this->syncItems($club, $group, is_array($items) ? $items : []);
        }

        return MemberResponse::success('Competition configuration saved successfully.', null);
    }
}
