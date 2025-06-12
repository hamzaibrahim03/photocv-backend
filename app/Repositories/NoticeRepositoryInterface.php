<?php

namespace App\Repositories;

interface NoticeRepositoryInterface
{
    public function all( $request );
    public function create(array $data, $images, $document);
    public function show( $id );
    public function update($id, array $data, $images, $document);
    public function delete($id);
    public function getNoticeExtras( $request );
}
