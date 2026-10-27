<?php

namespace App\Http\Controllers\v1\Super;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\Club;
use App\Models\Comment;
use App\Models\Competition;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\User;
use App\Services\Profile\ProfileFormatterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SuperProfileController extends Controller
{
    private ProfileFormatterService $profileFormatter;

    public function __construct(ProfileFormatterService $profileFormatter)
    {
        $this->profileFormatter = $profileFormatter;
    }

    /**
     * Platform-wide counts + recent content for SuperDashboard's stat pills
     * and "Latest Club Galleries" / "Recent Photos" sections.
     */
    public function dashboardStats()
    {
        $memberRoleId = Role::where('name', 'member')->value('id');
        $photographersCount = $memberRoleId
            ? DB::table('model_has_roles')->where('role_id', $memberRoleId)->count()
            : 0;

        $imagesCount = Photo::count();
        $commentsCount = Comment::where('record_type', 'photo')->where('comment_type', 'comment')->count();
        $likesCount = Comment::where('record_type', 'photo')->where('comment_type', 'liking')->count();

        $latestGalleries = Gallery::with('firstPhoto')
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(function ($gallery) {
                $club = Club::find($gallery->club_id);
                return [
                    'id' => $gallery->id,
                    'label' => $gallery->gallery_name,
                    'club' => $club?->club_name ?? 'Unknown club',
                    'image' => $gallery->firstPhoto?->image_url,
                ];
            })
            ->values();

        $recentPhotos = Photo::with('gallery')
            ->withCount(['likes', 'comments'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(function ($photo) {
                $club = $photo->gallery ? Club::find($photo->gallery->club_id) : null;
                return [
                    'id' => $photo->id,
                    'label' => $photo->title ?? ($club?->club_name ?? 'Photo'),
                    'club' => $club?->club_name,
                    'image' => $photo->image_url,
                    'likes_count' => $photo->likes_count,
                    'comments_count' => $photo->comments_count,
                ];
            })
            ->values();

        return MemberResponse::success('Dashboard stats retrieved successfully.', [
            'photographersCount' => $photographersCount,
            'imagesCount' => $imagesCount,
            'commentsCount' => $commentsCount,
            'likesCount' => $likesCount,
            'latestGalleries' => $latestGalleries,
            'recentPhotos' => $recentPhotos,
        ]);
    }

    /**
     * Platform-wide events + competitions happening this week (Mon-Sun),
     * pre-bucketed by weekday for SuperDashboard's "Events"/"Competitions"
     * bar charts. Replaces the old single-club public-home fetch so every
     * club's data is reflected, not just one hardcoded subdomain.
     */
    public function dashboardCalendar()
    {
        $start = now()->startOfWeek()->startOfDay();
        $end = now()->endOfWeek()->endOfDay();

        $events = Event::whereBetween('event_date', [$start, $end])->get(['id', 'event_date']);
        $competitions = Competition::whereBetween('start_date', [$start, $end])->get(['id', 'start_date']);

        $eventsByDay = array_fill(0, 7, 0);
        foreach ($events as $event) {
            if (!$event->event_date) {
                continue;
            }
            $eventsByDay[$event->event_date->dayOfWeekIso - 1] += 1;
        }

        $competitionsByDay = array_fill(0, 7, 0);
        foreach ($competitions as $competition) {
            if (!$competition->start_date) {
                continue;
            }
            $competitionsByDay[$competition->start_date->dayOfWeekIso - 1] += 1;
        }

        return MemberResponse::success('Dashboard calendar retrieved successfully.', [
            'eventsByDay' => array_values($eventsByDay),
            'competitionsByDay' => array_values($competitionsByDay),
            'totalEvents' => $events->count(),
            'totalCompetitions' => $competitions->count(),
        ]);
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $term = $request->query('search');
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        $users = $query->orderByDesc('created_at')->get();
        $profiles = $users->map(fn ($user) => $this->profileFormatter->format($user));

        return MemberResponse::success('Profiles retrieved successfully.', $profiles);
    }

    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        return MemberResponse::success('Profile retrieved successfully.', $this->profileFormatter->format($user, true));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string|max:255',
            'bio' => 'sometimes|nullable|string',
            'about' => 'sometimes|nullable|string',
            'status' => 'sometimes|boolean',
        ]);

        if (array_key_exists('status', $data)) {
            $data['account_status'] = $data['status'] ? 'active' : 'inactive';
            unset($data['status']);
        }

        $user->update($data);

        return MemberResponse::success('Profile updated successfully.', $this->profileFormatter->format($user->fresh(['roles']), true));
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return MemberResponse::success('Profile deleted successfully.', null);
    }

}
