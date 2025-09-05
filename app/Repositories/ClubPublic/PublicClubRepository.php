<?php

namespace App\Repositories\ClubPublic;
use App\Models\User;
use App\Models\Photo;
use Carbon\Carbon;

class PublicClubRepository implements PublicClubRepositoryInterface
{
    protected ClubRepositories $repos;

    public function __construct(ClubRepositories $repos)
    {
        $this->repos = $repos;
    }

    /**
     * Method to get club public home data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubHomeData($request)
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
        ->whereHas('comments')
        ->orWhereHas('likes')
        ->latest()
        ->get();


        return response()->json([
            'success' => true,
            'message' => 'Data retreived successfully',
            'data' => [
                'user'                 => $user,
                'clubGalleries'        => $this->repos->clubGalleryRepo->getClubGalleries($user->id),
                'upcomingEvents'       => $this->repos->eventRepo->getUpcomingEvents($clubId),
                'upcomingCompetitions' => $this->repos->competitionRepo->getLatestCompetitionsByLimit($clubId),
                'calendar'             => $this->repos->competitionRepo->getCalenderCompetitionData($clubId, $startOfMonth, $endOfMonth),
                'memberGalleries'      => $this->repos->clubDashboardRepo->getMembersGallerries($clubId),
                'latestMembers'        => $this->repos->clubDashboardRepo->getLatestMembers($clubId),
                'latestInteractions'   => $photos->map(function ($photo) {
                    return [
                        'id'           => $photo->id,
                        'title'        => $photo->title,
                        'image_url'    => $photo->image_url,
                        'description'  => $photo->description,
                        'uploaded_by'  => [
                            'id'       => $photo->uploadedBy?->id,
                            'username' => $photo->uploadedBy?->username,
                            'email'    => $photo->uploadedBy?->email,
                        ],
                        'comments' => $photo->comments->map(function ($comment) {
                            return [
                                'id'      => $comment->id,
                                'comment' => $comment->comment,
                                'interacted_by' => [
                                    'id'       => $comment->user?->id,
                                    'username' => $comment->user?->username,
                                ],
                                'created_at' => $comment->created_at,
                            ];
                        }),
                        'likes' => $photo->likes->map(function ($like) {
                            return [
                                'id'            => $like->id,
                                'interacted_by' => [
                                    'id'       => $like->user?->id,
                                    'username' => $like->user?->username,
                                ],
                                'created_at' => $like->created_at,
                            ];
                        }),
                    ];
                }),
                'clubNews'      => $this->repos->clubNewsRepo->getClubNews($clubId),
                'latest_notice' => $this->repos->noticeRepository->getLatestNotice($clubId),
                'latestResults' => $this->repos->competitionResultRepository->getAllPublishedResults($clubId),
                'clubSettings'  => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
            ]
        ], 200);
    }

    /**
     * Method to get club public event data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubEventData($request)
    {
        $username = $request->route('username');
        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Event data retrieved successfully',
            'data' => [
                'clubSettings'  => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'events'       => $this->repos->eventRepo->all($request, $user->id),
            ]
        ], 200);
    }

    /**
     * Method to get club public single event data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubSingleEventData($request)
    {
        $username = $request->route('username');
        $user     = User::where('username', $username)->firstOrFail();
        $eventId  = $request->event_id;

        return response()->json([
            'success' => true,
            'message' => 'Event data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'event'        => $this->repos->eventRepo->getEventById($eventId, $user->id),
            ]
        ], 200);
    }

    /**
     * Method to get club public upcoming competition data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubCompetitionData($request)
    {
        $username = $request->route('username');
        $user = User::where('username', $username)->firstOrFail();

        $clubId = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'Competition data retrieved successfully',
            'data' => [
                'clubSettings'         => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'upcomingCompetitions' => $this->repos->competitionRepo->getLatestCompetitionsByLimit($clubId, null),
            ]
        ], 200);
    }

    /**
     * Method to get club public single competition data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubSingleCompetitionData($request)
    {
        $username = $request->route('username');
        $user     = User::where('username', $username)->firstOrFail();
        $competitionId = $request->competition_id;

        return response()->json([
            'success' => true,
            'message' => 'Competition data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'competition'  => $this->repos->competitionRepo->show($competitionId, $user->id),
            ]
        ], 200);
    }

    /**
     * Method to get club & member galleries for public view
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubGalleries($request)
    {
        $username = $request->route('username');
        $user     = User::where('username', $username)->firstOrFail();
        $clubId   = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'Galleries data retrieved successfully',
            'data' => [
                'clubSettings'    => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'clubGalleries'   => $this->repos->clubGalleryRepo->getClubGalleries($user->id),
                'memberGalleries' => $this->repos->clubDashboardRepo->getMembersGallerries($clubId),
            ]
        ], 200);
    }

    /**
     * Method to get club public news data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubNewsData($request)
    {
        $username = $request->route('username');
        $user     = User::where('username', $username)->firstOrFail();
        $clubId   = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'News data retrieved successfully',
            'data' => [
                'clubSettings'   => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'clubNews'       => $this->repos->clubNewsRepo->getClubNews($clubId),
                'upcomingEvents' => $this->repos->eventRepo->getUpcomingEvents($clubId),
            ]
        ], 200);
    }

    /**
     * Method to get club public single news data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClubSingleNewsData($request)
    {
        $username = $request->route('username');
        $user     = User::where('username', $username)->firstOrFail();
        $clubId   = $user->club->id;
        $newsId   = $request->news_id;

        return response()->json([
            'success' => true,
            'message' => 'News data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'news'         => $this->repos->clubNewsRepo->getNewsById($newsId, $user->id),
                'upcomingEvents' => $this->repos->eventRepo->getUpcomingEvents($clubId),
            ]
        ], 200);
    }

}
