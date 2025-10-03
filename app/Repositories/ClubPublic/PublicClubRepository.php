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

        // Fetch only photos that have comments or likes
        $photos = Photo::with([
            'uploadedBy:id,username,email',

            'comments' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'comments.user:id,username,email',

            'likes' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
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
                'calendar'             => $this->repos->competitionRepo->getCalenderCompetitionAndEvent($clubId),
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
                        'comments' => $photo->comments->sortByDesc('created_at')->map(function ($comment) {
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
                        'likes' => $photo->likes->sortByDesc('created_at')->map(function ($like) {
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
        $user     = User::where('username', $username)->firstOrFail();
        $clubId   = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'Event data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'events'       => $this->repos->eventRepo->all($request, $user->id),
                'calendar'     => $this->repos->competitionRepo->getCalenderCompetitionAndEvent($clubId),
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
        $clubId   = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'Event data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'event'        => $this->repos->eventRepo->getEventById($eventId, $user->id),
                'calendar'     => $this->repos->competitionRepo->getCalenderCompetitionAndEvent($clubId),
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
                'calendar'             => $this->repos->competitionRepo->getCalenderCompetitionAndEvent($clubId),
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
        $username      = $request->route('username');
        $user          = User::where('username', $username)->firstOrFail();
        $competitionId = $request->competition_id;
        $clubId        = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'Competition data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'calendar'     => $this->repos->competitionRepo->getCalenderCompetitionAndEvent($clubId),
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

        // check if user asked for a specific dataset
        $only = $request->query('only'); // values: "memberGalleries", "clubGalleries"

        $data = [];

        // if (!$only || $only === 'clubSettings') {
            $data['clubSettings'] = $this->repos->clubSettingRepo->getAllClubSettings($user->id);
        // }

        if (!$only || $only === 'clubGalleries') {
            $data['clubGalleries'] = $this->repos->clubGalleryRepo->getClubGalleries($user->id);
        }

        if (!$only || $only === 'memberGalleries') {
            $data['memberGalleries'] = $this->repos->clubDashboardRepo->getMembersGallerries($clubId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Galleries data retrieved successfully',
            'data'    => $data
        ], 200);
    }

    /**
     * Method to get single club gallery information
     * @param mixed $request
     * @param mixed $username
     * @param mixed $galleryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function clubGallery($request, $username, $galleryId)
    {
        $user = User::where('username', $username)->firstOrFail();
        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'clubGallery'  => $this->repos->clubGalleryRepo->getClubGallery($galleryId),
            ]
        ], 200);
    }

    /**
     * Method to get single member galleries
     * @param mixed $request
     * @param mixed $username
     * @param mixed $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberGalleries($request, $username, $memberId)
    {
        $user = User::where('username', $username)->firstOrFail();
        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => [
                'clubSettings'   => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'memberGalleries'       => $this->repos->memberRepository->memberGalleryDetails($memberId),
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
                'clubNews'       => $this->repos->clubNewsRepo->index($request, $clubId),
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

    /**
     * Method to return about us public page data
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function aboutUs($request)
    {
        $username = $request->route('username');
        $user     = User::where('username', $username)->firstOrFail();
        $clubId   = $user->club->id;

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => [
                'clubSettings' => $this->repos->clubSettingRepo->getAllClubSettings($user->id),
                'aboutUs'      => $this->repos->pageRepo->getBySlug('about-us'),
                'clubOfficials'      => $this->repos->memberRepository->getMembersByClub($clubId),
            ]
        ], 200);
    }

}
