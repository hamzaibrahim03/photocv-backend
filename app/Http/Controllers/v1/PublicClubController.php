<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ClubDashboardRepository;
use App\Repositories\EventRepository;
use App\Repositories\CompetitionRepository;
use App\Repositories\ClubNewsRepository;
use App\Repositories\ClubSettingsRepository;
use App\Models\User;
use App\Models\MemberPhoto;
use Carbon\Carbon;

class PublicClubController extends Controller
{
    protected ClubDashboardRepository $clubDashboardRepo;
    protected EventRepository $eventRepo;
    protected CompetitionRepository $competitionRepo;
    protected ClubNewsRepository $clubNewsRepo;
    protected ClubSettingsRepository $clubSettingRepo;

    public function __construct(
        ClubDashboardRepository $clubDashboardRepo,
        EventRepository $eventRepo,
        CompetitionRepository $competitionRepo,
        ClubNewsRepository $clubNewsRepo,
        ClubSettingsRepository $clubSettingRepo,
    ) {
        $this->clubDashboardRepo = $clubDashboardRepo;
        $this->eventRepo = $eventRepo;
        $this->competitionRepo = $competitionRepo;
        $this->clubNewsRepo = $clubNewsRepo;
        $this->clubSettingRepo = $clubSettingRepo;
    }

    public function getClubData(Request $request)
    {
        $username = $request->route('username');
        $user = User::where('username', $username)->firstOrFail();

        $clubId = $user->club->id;

        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : Carbon::now()->endOfMonth();


        return response()->json([
            'success' => true,
            'message' => 'Data retreived successfully',
            'data' => [
                'user' => $user,
                'clubGallery' => $user->galleries,
                'memberGalleries' => $this->clubDashboardRepo->getMembersGallerries($clubId),
                'latestMembers' => $this->clubDashboardRepo->getLatestMembers($clubId),
                'upcomingEvents' => $this->eventRepo->getUpcomingEvents($clubId),
                'upcomingCompetitions' => $this->competitionRepo->getUpcomingCompetitions($clubId),
                'calendar' => $this->competitionRepo->getCalenderCompetitionData($clubId, $startOfMonth, $endOfMonth),
                'recentComments' => $user->comments()
                    ->latest()
                    ->get()
                    ->map(function ($comment) {
                        $related = match ($comment->record_type) {
                            'event'  => $comment->event,
                            'page'   => $comment->page,
                            'notice' => $comment->memberNotice,
                            'news'   => $comment->clubNews,
                            default  => null,
                        };

                        $comment->related_record_name = optional($related)->title ?? optional($related)->name ?? null;

                        return $comment;
                    }),
                'recentLikes' => MemberPhoto::whereHas('likes')
                    ->whereHas('gallery', function ($query) use ($user) {
                        $query->where('member_id', $user->id);
                    })
                    ->latest()
                    ->get(),
                'clubNews' => $this->clubNewsRepo->getClubNews($clubId),
                'clubSettings' => $this->clubSettingRepo->all($user->id),
            ]
        ], 200);
    }
}
