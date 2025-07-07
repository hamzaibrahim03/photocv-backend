<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemberAdminService;

class MemberAdminController extends Controller
{
    private $memberAdminService;

    public function __construct(MemberAdminService $memberAdminService)
    {
        $this->memberAdminService = $memberAdminService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function memberEvents( Request $request )
    {
        $data = $this->memberAdminService->memberEvents( $request );
        return response()->json($data);
    }
    
    /**
     * Method to get single event details with extra information
     * @param mixed $id
     */
    public function memberSingleEvent( $id )
    {
        return $this->memberAdminService->memberSingleEvent( $id );
    }

    /**
     * Method to get member admin competition data with extra information
     * @return void
     */
    public function memberCompetitions(Request $request)
    {
        return $this->memberAdminService->memberCompetitions( $request );
    }

    /**
     * Method to load single competition with extra information
     * @param mixed $id
     * @return void
     */
    public function memberSingleCompetition($id)
    {
        return $this->memberAdminService->memberSingleCompetition($id);
    }

    /**
     * Method to get gallery and notices information
     * @return void
     */
    public function memberProfilePostsView()
    {
        return $this->memberAdminService->memberProfilePostsView();
    }

    /**
     * Method to get profile data
     * @return array{gallery: mixed, member_info: mixed, my_awards: mixed, profile_extras: mixed, recent_comments_and_interactions: mixed, recent_competition_submissions: mixed}
     */
    public function memberAdminProfile()
    {
        return $this->memberAdminService->memberAdminProfile();
    }

    public function memberAdminProfilePortfolio()
    {
        return $this->memberAdminService->memberAdminProfilePortfolio();
    }
}
