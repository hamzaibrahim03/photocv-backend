<?php

namespace App\Repositories;

interface NoticeRepositoryInterface
{
    public function all( $request, $userId );
    public function create(array $data, $images, $document);
    public function show( $id, $userId );
    public function update($id, array $data, $images, $document);
    public function delete($id);
    public function getNoticeExtras( $request );
    public function getLatestNotice($clubId);
    public function getLatestNoticeWithLimit($clubId, $limit);
}
