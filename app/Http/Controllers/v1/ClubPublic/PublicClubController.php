<?php

namespace App\Http\Controllers\v1\ClubPublic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubPublic\PublicClubService;

class PublicClubController extends Controller
{
    private $publicClubService;

    public function __construct(PublicClubService $publicClubService)
    {
        $this->publicClubService = $publicClubService;
    }

    /**
     * Method to get club public home data
     * @param \Illuminate\Http\Request $request
     */
    public function home(Request $request)
    {
        return $this->publicClubService->getClubHomeData($request);
    }

    /**
     * Method to get club public event data
     * @param \Illuminate\Http\Request $request
     */
    public function eventIndex(Request $request)
    {
        return $this->publicClubService->getClubEventData($request);
    }

    /**
     * Method to get club public event data
     * @param \Illuminate\Http\Request $request
     */
    public function event(Request $request, $id)
    {
        return $this->publicClubService->getClubSingleEventData($request);
    }

    /**
     * Method to get club public upcoming competition data
     * @param \Illuminate\Http\Request $request
     */
    public function competitionIndex(Request $request)
    {
        return $this->publicClubService->getClubCompetitionData($request);
    }

    /**
     * Method to get club public single competition data
     * @param \Illuminate\Http\Request $request
     */
    public function competition(Request $request)
    {
        return $this->publicClubService->getClubSingleCompetitionData($request);
    }

    /**
     * Method to get club public galleries data
     * @param \Illuminate\Http\Request $request
     */
    public function galleries(Request $request)
    {
        return $this->publicClubService->getClubGalleries($request);
    }

    /**
     * Method to get single member gallery information
     * @param \Illuminate\Http\Request $request
     * @param mixed $username
     * @param mixed $memberId
     */
    public function memberGalleries(Request $request, $username, $memberId)
    {
        return $this->publicClubService->memberGalleries($request, $username, $memberId);
    }

    /**
     * Method to get single gallery information
     * @param \Illuminate\Http\Request $request
     * @param mixed $username
     * @param mixed $galleryId
     */
    public function clubGallery(Request $request, $username, $galleryId)
    {
        return $this->publicClubService->clubGallery($request, $username, $galleryId);
    }

    /**
     * Method to get random club galleries
     * @param \Illuminate\Http\Request $request
     */
    public function randomClubGalleries(Request $request)
    {
        return $this->publicClubService->getRandomClubGalleries($request);
    }

    /**
     * Method to get random member galleries
     * @param \Illuminate\Http\Request $request
     */
    public function randomMemberGalleries(Request $request)
    {
        return $this->publicClubService->getRandomMemberGalleries($request);
    }


    /**
     * Method to get club public news data
     * @param \Illuminate\Http\Request $request
     */
    public function newsIndex(Request $request)
    {
        return $this->publicClubService->getClubNewsData($request);
    }

    /**
     * Method to get club public single news data
     * @param \Illuminate\Http\Request $request
     */
    public function newsSingle(Request $request)
    {
        return $this->publicClubService->getClubSingleNewsData($request);
    }

    /**
     * Method to get about us public page data
     * @param \Illuminate\Http\Request $request
     */
    public function aboutUs(Request $request)
    {
        return $this->publicClubService->aboutUs($request);
    }

    /**
     * Method to get club public notices data
     * @param \Illuminate\Http\Request $request
     */
    public function noticeIndex(Request $request)
    {
        return $this->publicClubService->getClubNoticesData($request);
    }

    /**
     * Method to get club public single notice data
     * @param \Illuminate\Http\Request $request
     */
    public function noticeSingle(Request $request, $username, $id)
    {
        return $this->publicClubService->getClubSingleNotice($request, $username, $id);
    }

}
