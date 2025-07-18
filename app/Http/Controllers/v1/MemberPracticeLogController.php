<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemberPracticeLogService;
use App\Http\Requests\Member\MemberPracticeLogRequest;

class MemberPracticeLogController extends Controller
{

    private $memberPracticeLogService;

    public function __construct(MemberPracticeLogService $memberPracticeLogService)
    {
        $this->memberPracticeLogService = $memberPracticeLogService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->memberPracticeLogService->index( $request );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberPracticeLogRequest $request)
    {
        return $this->memberPracticeLogService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
