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

}
