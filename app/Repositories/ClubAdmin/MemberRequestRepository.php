<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Club;
use App\Models\User;
use App\Traits\DataTables\MemberDataTableTrait;
use App\Http\Responses\MemberResponse;

class MemberRequestRepository implements MemberRequestRepositoryInterface
{
    use MemberDataTableTrait;

    /**
     * Assign a club to a member.
     *
     * @param  int  $userId
     * @param  int  $clubId
     * @return bool
     */
    public function assignClubToUser($userId, $clubId)
    {
        $user = User::findOrFail($userId);
        Club::findOrFail($clubId);

        $user->clubs()->updateExistingPivot($clubId, ['status' => 'approved', 'joined_at' => now()]);
        return true;
    }

    /**
     * Get all pending member requests for the club admin's club.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPendingRequests($request)
    {
        try {
            $members = $this->getAllMemberPendingRequests($request);
            return MemberResponse::success('Members retrieved successfully.', $members);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get details of a specific member who requested to join the club.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestingMember($userId)
    {
        try {
            $club = auth()->user()->club;

            if (!$club) {
                return MemberResponse::error('You are not assigned to any club.');
            }

            // Get the specific user who requested to join this club
            $member = $club->users()
                        ->where('users.id', $userId)
                        ->wherePivot('status', 'pending')
                        ->first();

            if (!$member) {
                return MemberResponse::error('No pending request found for this member in your club.');
            }

            // Total pending requests in the club
            $totalPending = $club->users()->wherePivot('status', 'pending')->count();

            return MemberResponse::success(
                'Member request retrieved successfully.',
                [
                    'member' => $member,
                    'social_links' => $member->socialLinks,
                    'total_pending_requests' => $totalPending
                ]
            );
        } catch (\Exception $e) {
            return MemberResponse::error('Failed to fetch member request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a member's request to join the club.
     *
     * @param  array  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectClubRequest($data)
    {
        try {
            $club = auth()->user()->club;

            if (! $club) {
                return MemberResponse::error('You are not assigned to any club.');
            }

            $userId = $data['user_id'];

            // Check if this user actually has a pending request in the club
            $isPending = $club->users()
                            ->where('users.id', $userId)
                            ->wherePivot('status', 'pending')
                            ->exists();

            if (! $isPending) {
                return MemberResponse::error('No pending request found for this member in your club.');
            }

            // Update pivot to rejected
            $club->users()->updateExistingPivot($userId, [
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            return MemberResponse::success('Member request has been rejected successfully.');
        } catch (\Exception $e) {
            return MemberResponse::error('Failed to reject request: ' . $e->getMessage());
        }
    }
}
