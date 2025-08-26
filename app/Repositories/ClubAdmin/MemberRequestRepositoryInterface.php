<?php

namespace App\Repositories\ClubAdmin;

interface MemberRequestRepositoryInterface
{
    public function assignClubToUser($userId, $clubId);
    public function getAllPendingRequests();
    public function getRequestingMember($userId);
    public function rejectClubRequest($data);
}
