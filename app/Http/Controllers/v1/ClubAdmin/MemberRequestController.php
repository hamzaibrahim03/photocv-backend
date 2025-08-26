<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubAdmin\MemberRequestService;

class MemberRequestController extends Controller
{
    private $memberRequestService;

    public function __construct(MemberRequestService $memberRequestService)
    {
        $this->memberRequestService = $memberRequestService;
    }

    /**
     * Assign a club to a member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignClub(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'club_id' => 'required|exists:clubs,id',
        ]);

        $this->memberRequestService->assignMember($validated['user_id'], $validated['club_id']);

        return response()->json(['message' => 'Club assigned successfully']);
    }

    public function pendingRequests(Request $request)
    {
        return $this->memberRequestService->pendingRequests($request);
    }

    public function getRequestingMember($userId)
    {
        return $this->memberRequestService->getRequestingMember($userId);
    }

    public function rejectClubRequest(Request $request)
    {
        return $this->memberRequestService->rejectClubRequest($request->all());
    }
}
