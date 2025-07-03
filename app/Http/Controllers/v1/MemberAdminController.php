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
        return $this->memberAdminService->memberEvents( $request );
    }
    
    public function memberSingleEvent( $id )
    {
        return $this->memberAdminService->memberSingleEvent( $id );
    }
}
