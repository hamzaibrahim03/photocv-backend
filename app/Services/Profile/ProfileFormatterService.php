<?php

namespace App\Services\Profile;

use App\Models\Club;
use App\Models\Comment;
use App\Models\CompetitionMembersEntry;
use App\Models\Gallery;
use App\Models\MemberAward;
use App\Models\MemberClub;
use App\Models\MemberSocialLink;
use App\Models\Photo;
use App\Models\User;

/**
 * Builds the full profile shape (avatar, bio, clubs, comments, competition
 * entries, awards, gallery) shared by any screen that shows a member's
 * profile in detail - originally SuperProfileController's admin "view any
 * member" page, now also the photographer's own dashboard.
 */
class ProfileFormatterService
{
    public function format(User $user, bool $detailed = false): array
    {
        $roleName = $user->roles->first()->name ?? 'General Members';
        $club = Club::with('setting')->where('user_id', $user->id)->first();

        // club_admin users own their club directly; everyone else joins one
        // via member_clubs, which is also where their "established" (join)
        // year comes from.
        $establishedYear = null;
        if ($club) {
            $establishedYear = $club->established_year;
        } else {
            $memberClub = MemberClub::where('member_id', $user->id)->latest('joining_date')->first();
            if ($memberClub) {
                $club = Club::with('setting')->find($memberClub->club_id);
                $establishedYear = $memberClub->joining_date?->format('Y');
            }
        }

        $galleries = Gallery::where('member_id', $user->id)->with('firstPhoto')->get();
        $firstGalleryPhoto = $galleries->first(fn ($g) => $g->firstPhoto)?->firstPhoto;
        $photoIds = Photo::whereIn('gallery_id', $galleries->pluck('id'))->pluck('id');

        $base = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
            'role' => $roleName,
            'category' => $roleName === 'club_admin' ? 'Club Admin' : ($roleName === 'member' ? 'Photographer' : 'Club Members'),
            'clubId' => $club?->id,
            'clubName' => $club?->club_name,
            'avatar' => $user->profile_image_url,
            'photoCount' => $photoIds->count(),
            'commentsCount' => Comment::whereIn('record_id', $photoIds)->where('record_type', 'photo')->where('comment_type', 'comment')->count(),
            'likesCount' => Comment::whereIn('record_id', $photoIds)->where('record_type', 'photo')->where('comment_type', 'liking')->count(),
            'galleryThumb' => $firstGalleryPhoto?->image_url,
            'portfolioThumb' => $galleries->flatMap(fn ($g) => $g->photos()->where('show_in_portfolio', true)->first() ? [$g->photos()->where('show_in_portfolio', true)->first()] : [])->first()?->image_url,
            'templatePreview' => $club?->setting?->template_preview_url,
            'establishedYear' => $establishedYear,
            'status' => $user->account_status !== 'inactive',
            'website' => $club?->domain_name ? "http://{$club->domain_name}" : null,
        ];

        if (!$detailed) {
            return $base;
        }

        $galleryCount = $galleries->count();
        $photoCount = $photoIds->count();

        $memberClubs = MemberClub::where('member_id', $user->id)->get();
        $clubsById = Club::whereIn('id', $memberClubs->pluck('club_id'))->with('setting')->get()->keyBy('id');
        $clubs = $memberClubs
            ->map(function ($mc) use ($clubsById) {
                $club = $clubsById->get($mc->club_id);
                return [
                    'name' => $club?->club_name,
                    'since' => $mc->joining_date ? date('Y', strtotime($mc->joining_date)) : null,
                    'image' => $club?->setting?->logo_url,
                ];
            })
            ->filter(fn ($c) => $c['name'])
            ->values();

        $comments = Comment::with('user')
            ->whereIn('record_id', $photoIds)
            ->where('record_type', 'photo')
            ->where('comment_type', 'comment')
            ->whereNotNull('comment')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($c) => [
                'name' => $c->user ? (trim(($c->user->first_name ?? '') . ' ' . ($c->user->last_name ?? '')) ?: $c->user->username) : 'Unknown',
                'text' => $c->comment,
                'time' => $c->created_at->diffForHumans(),
                'image' => $c->user?->profile_image_url,
            ]);

        // Gallery photo awards come from an explicit MemberAward record;
        // competition entry awards are derived from podium placement
        // (position 1-3) since entries have no separate award table.
        $galleryAwardsByPhotoId = MemberAward::where('member_id', $user->id)
            ->get()
            ->keyBy('photo_id')
            ->map(fn ($a) => ['standing' => $a->award_standing, 'date' => $a->award_date]);

        $podiumLabels = [1 => '1st Place', 2 => '2nd Place', 3 => '3rd Place'];

        $entries = CompetitionMembersEntry::whereHas('competitionMember', fn ($q) => $q->where('member_id', $user->id))
            ->with('competitionMember.competition')
            ->withCount(['likes', 'comments'])
            ->get()
            ->groupBy(fn ($e) => $e->competitionMember?->competition?->name ?? 'Competition')
            ->map(fn ($group, $name) => [
                'name' => $name,
                'images' => $group->map(fn ($e) => [
                    'image' => $e->entry_image_url,
                    'likesCount' => $e->likes_count,
                    'commentsCount' => $e->comments_count,
                    'award' => isset($podiumLabels[$e->position]) ? ['standing' => $podiumLabels[$e->position]] : null,
                ])->filter(fn ($i) => $i['image'])->values(),
            ])
            ->values();

        $galleryList = $galleries->flatMap(function ($g) use ($galleryAwardsByPhotoId) {
            return $g->photos()->withCount(['likes', 'comments'])->get()->map(fn ($p) => [
                'label' => $g->gallery_name,
                'image' => $p->image_url,
                'likesCount' => $p->likes_count,
                'commentsCount' => $p->comments_count,
                'award' => $galleryAwardsByPhotoId->get($p->id),
            ]);
        })->values();

        // Unified awards summary: explicit gallery-photo awards + podium
        // placements across all competition entries.
        $galleryAwards = $galleryList->filter(fn ($p) => $p['award'])->map(fn ($p) => [
            'standing' => $p['award']['standing'],
            'date' => $p['award']['date'] ?? null,
            'image' => $p['image'],
        ]);
        $entryAwards = $entries->flatMap(fn ($group) => collect($group['images'])->filter(fn ($i) => $i['award'])->map(fn ($i) => [
            'standing' => $i['award']['standing'],
            'date' => null,
            'image' => $i['image'],
        ]));
        $awards = $galleryAwards->concat($entryAwards)->values();

        $socialLinks = MemberSocialLink::where('member_id', $user->id)
            ->get(['social_media_name', 'social_link']);

        return array_merge($base, [
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'bio' => $user->bio,
            'about' => $user->about,
            'status' => $user->account_status !== 'inactive',
            'galleryCount' => $galleryCount,
            'photoCount' => $photoCount,
            'galleries' => $galleryList,
            'clubs' => $clubs,
            'comments' => $comments,
            'entries' => $entries,
            'awards' => $awards,
            'socialLinks' => $socialLinks,
        ]);
    }
}
