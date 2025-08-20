<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ClubAdmin\ClubDashboardRepository;
use App\Repositories\ClubAdmin\EventRepository;
use App\Repositories\ClubAdmin\CompetitionRepository;
use App\Repositories\ClubAdmin\ClubNewsRepository;
use App\Repositories\ClubAdmin\ClubSettingsRepository;
use App\Repositories\ClubAdmin\ClubGalleryRepository;
use App\Repositories\ClubAdmin\CompetitionResultRepositoryInterface;
use App\Models\User;
use App\Models\Photo;
use Carbon\Carbon;

class PublicClubController extends Controller
{
    protected ClubDashboardRepository $clubDashboardRepo;
    protected EventRepository $eventRepo;
    protected CompetitionRepository $competitionRepo;
    protected ClubNewsRepository $clubNewsRepo;
    protected ClubSettingsRepository $clubSettingRepo;
    protected ClubGalleryRepository $clubGalleryRepo;
    protected $competitionResultRepository;

    public function __construct(
        ClubDashboardRepository $clubDashboardRepo,
        EventRepository $eventRepo,
        CompetitionRepository $competitionRepo,
        ClubNewsRepository $clubNewsRepo,
        ClubSettingsRepository $clubSettingRepo,
        ClubGalleryRepository $clubGalleryRepo,
        CompetitionResultRepositoryInterface $competitionResultRepository
    ) {
        $this->clubDashboardRepo = $clubDashboardRepo;
        $this->eventRepo = $eventRepo;
        $this->competitionRepo = $competitionRepo;
        $this->clubNewsRepo = $clubNewsRepo;
        $this->clubSettingRepo = $clubSettingRepo;
        $this->clubGalleryRepo = $clubGalleryRepo;
        $this->competitionResultRepository = $competitionResultRepository;
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

        // Fetch only photos that have comments or likes
        $photos = Photo::with([
            'uploadedBy:id,username,email',
            'comments.user:id,username,email',
            'likes.user:id,username,email'
        ])
        ->whereHas('comments') // Only photos that have comments
        ->orWhereHas('likes')  // Or have likes
        ->latest()
        ->get();


        return response()->json([
            'success' => true,
            'message' => 'Data retreived successfully',
            'data' => [
                'user' => $user,
                'clubGalleries' => $this->clubGalleryRepo->getClubGalleries($user->id),
                // 'clubGalleries' => MemberAward::with([
                //         'photo.gallery' => function ($query) {
                //             $query->with('member.clubs');
                //         }
                //     ])->get(),
                'upcomingEvents' => $this->eventRepo->getUpcomingEvents($clubId),
                'upcomingCompetitions' => $this->competitionRepo->getUpcomingCompetitions($clubId),
                'calendar' => $this->competitionRepo->getCalenderCompetitionData($clubId, $startOfMonth, $endOfMonth),
                'memberGalleries' => $this->clubDashboardRepo->getMembersGallerries($clubId),
                'latestMembers' => $this->clubDashboardRepo->getLatestMembers($clubId),
                'latestInteractions' => $photos->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'title' => $photo->title,
                        'image_url' => $photo->image_url,
                        'description' => $photo->description,
                        'uploaded_by' => [
                            'id' => $photo->uploadedBy?->id,
                            'username' => $photo->uploadedBy?->username,
                            'email' => $photo->uploadedBy?->email,
                        ],
                        'comments' => $photo->comments->map(function ($comment) {
                            return [
                                'id' => $comment->id,
                                'comment' => $comment->comment,
                                'interacted_by' => [
                                    'id' => $comment->user?->id,
                                    'username' => $comment->user?->username,
                                ],
                                'created_at' => $comment->created_at,
                            ];
                        }),
                        'likes' => $photo->likes->map(function ($like) {
                            return [
                                'id' => $like->id,
                                'interacted_by' => [
                                    'id' => $like->user?->id,
                                    'username' => $like->user?->username,
                                ],
                                'created_at' => $like->created_at,
                            ];
                        }),
                    ];
                }),
                'clubNews' => $this->clubNewsRepo->getClubNews($clubId),
                // 'latestReseults' => $this->clubDashboardRepo->getRecentResults($clubId),
                'latestReseults' => $this->competitionResultRepository->getAllPublishedResults($clubId),
                'clubSettings' => $this->clubSettingRepo->all($user->id),
            ]
        ], 200);
    }
}
