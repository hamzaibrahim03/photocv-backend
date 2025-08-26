<?php

namespace App\Repositories\ClubAdmin;

interface MemberRequestRepositoryInterface
{
    public function assignClubToUser($userId, $clubId);
    public function getAllPendingRequests($request);
    public function getRequestingMember($userId);
    public function rejectClubRequest($data);
}
