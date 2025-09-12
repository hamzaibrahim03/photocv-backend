<?php

namespace App\Repositories;

interface MemberRepositoryInterface
{
    public function all( $request );
    public function getMembersByClub($clubId);
    public function create(array $data, $files);
    public function show( $id );
    public function update($id, array $data, $files);
    public function delete($id);
    public function requestToJoinClub($user_id, $club_id);
    public function createGallery($data);
    public function memberGalleryImages($data);
    public function postCommentOrLikes($data);
    public function membersGalleries();
    public function memberGalleryDetails($memberId);
    public function profileUpdate($data);
    public function getJoinedClubs();
}
