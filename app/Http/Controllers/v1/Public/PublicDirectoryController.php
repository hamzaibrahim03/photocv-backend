<?php

namespace App\Http\Controllers\v1\Public;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\Club;
use App\Models\Comment;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Unauthenticated directory listings for the public marketing homepage
 * (src/marketing/Home.jsx). Trimmed-down, public-safe versions of
 * SuperProfileController/SuperClubController's data - no emails, no
 * status-toggle/edit/delete affordances, just what's safe to show a
 * visitor who isn't logged in.
 */
class PublicDirectoryController extends Controller
{
    public function members(Request $request)
    {
        $limit = min((int) $request->query('limit', 6), 24);

        $users = User::with('roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['member', 'club_admin']))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $profiles = $users->map(function (User $user) {
            $roleName = $user->roles->first()->name ?? 'member';
            $galleries = Gallery::where('member_id', $user->id)->with('firstPhoto')->get();
            $photoIds = Photo::whereIn('gallery_id', $galleries->pluck('id'))->pluck('id');

            $galleryPreview = $galleries
                ->filter(fn ($g) => $g->firstPhoto)
                ->take(2)
                ->map(fn ($g) => [
                    'id' => $g->firstPhoto->id,
                    'title' => $g->firstPhoto->title,
                    'image_url' => $g->firstPhoto->image_url,
                ])
                ->values();

            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
                'role' => $roleName === 'club_admin' ? 'Club Admin' : 'Photographer',
                'avatar' => $user->profile_image_url,
                'photoCount' => $photoIds->count(),
                'commentsCount' => Comment::whereIn('record_id', $photoIds)->where('record_type', 'photo')->where('comment_type', 'comment')->count(),
                'likesCount' => Comment::whereIn('record_id', $photoIds)->where('record_type', 'photo')->where('comment_type', 'liking')->count(),
                'galleryPreview' => $galleryPreview,
            ];
        });

        return MemberResponse::success('Profiles retrieved successfully.', $profiles);
    }

    public function clubs(Request $request)
    {
        $limit = min((int) $request->query('limit', 6), 24);

        $clubs = Club::with('setting')
            ->withCount('users')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Club $club) => [
                'id' => $club->id,
                'name' => $club->club_name,
                'tagline' => $club->tag_line,
                'established' => $club->established_year,
                'membersCount' => $club->users_count ?? 0,
                'logo' => $club->setting?->logo_url,
                'cover' => $club->setting?->club_banner_url,
            ]);

        return MemberResponse::success('Clubs retrieved successfully.', $clubs);
    }
}
