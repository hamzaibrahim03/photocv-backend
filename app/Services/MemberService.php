<?php
namespace App\Services;

use App\Repositories\MemberRepositoryInterface;

class MemberService
{
    private $memberRepository;

    public function __construct(MemberRepositoryInterface $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allMembers( $request )
    {
        return $this->memberRepository->all( $request );
    }

    /**
     * Show a specific member by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showMember( $id ) {
        return $this->memberRepository->show( $id );
    }

    /**
     * Create a new member.
     *
     * @param  array  $data
     * @param  mixed  $files
     * @return \Illuminate\Http\Response
     */
    public function createMember(array $data, $files)
    {
        return $this->memberRepository->create($data, $files);
    }

    /**
     * Update an existing member.
     *
     * @param  int  $id
     * @param  array  $data
     * @param  mixed  $files
     * @return \Illuminate\Http\Response
     */
    public function updateMember(int $id, array $data, $files)
    {
        return $this->memberRepository->update($id, $data, $files);
    }

    /**
     * Delete a member by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMember( $id )
    {
        return $this->memberRepository->delete($id);
    }

    public function requestToJoinClub($user_id, $club_id)
    {
        return $this->memberRepository->requestToJoinClub($user_id, $club_id);
    }

    public function pendingRequests()
    {
        return $this->memberRepository->getAllPendingRequests();
    }

    public function getRequestingMember($userId)
    {
        return $this->memberRepository->getRequestingMember($userId);
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
        return $this->memberRepository->assignClubToUser($user_id, $club_id);
    }

    /**
     * Create a new gallery for the member.
     *
     * @param  array  $data
     * @return \Illuminate\Http\Response
     */
    public function createGallery( $data )
    {
        return $this->memberRepository->createGallery( $data );
    }

    /**
     * Upload gallery images for a member.
     *
     * @param  array  $images
     * @return \Illuminate\Http\Response
     */
    public function memberGalleryImages($data) 
    {
        return $this->memberRepository->memberGalleryImages( $data );
    }

    public function postCommentOrLikes($data)
    {
        return $this->memberRepository->postCommentOrLikes($data);
    }

    public function membersGalleries()
    {
        return $this->memberRepository->membersGalleries();
    }

    public function memberGalleryDetails($memberId)
    {
        return $this->memberRepository->memberGalleryDetails($memberId);
    }

    public function profileUpdate($data)
    {
        return $this->memberRepository->profileUpdate($data);
    }

    /**
     * Method to return member's joined clubs
     */
    public function getJoinedClubs()
    {
        return $this->memberRepository->getJoinedClubs();
    }

    public function rejectClubRequest($data)
    {
        return $this->memberRepository->rejectClubRequest($data);
    }
}
