<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\MemberRequestRepositoryInterface;

class MemberRequestService
{
    private $memberRequestRepository;

    public function __construct(MemberRequestRepositoryInterface $memberRequestRepository)
    {
        $this->memberRequestRepository = $memberRequestRepository;
    }

    /**
     * Assign a club to a member.
     *
     * @param  int  $user_id
     * @param  int  $club_id
     * @return \Illuminate\Http\Response
     */
    public function assignMember( $user_id, $club_id )
    {
        return $this->memberRequestRepository->assignClubToUser($user_id, $club_id);
    }

    public function pendingRequests()
    {
        return $this->memberRequestRepository->getAllPendingRequests();
    }

    public function getRequestingMember($userId)
    {
        return $this->memberRequestRepository->getRequestingMember($userId);
    }

    public function rejectClubRequest($data)
    {
        return $this->memberRequestRepository->rejectClubRequest($data);
    }

}
