<?php

namespace App\Repositories;

interface MemberRepositoryInterface
{
    public function all( $request );
    public function create(array $data, $files);
    public function show( $id );
    public function update($id, array $data, $files);
    public function delete($id);
    public function assignClubToUser($userId, $clubId);
    public function createGallery($data);
    public function memberGalleryImages($data);
    public function postCommentOrLikes($data);
}
