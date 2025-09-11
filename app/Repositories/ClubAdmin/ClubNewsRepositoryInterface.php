<?php

namespace App\Repositories\ClubAdmin;

interface ClubNewsRepositoryInterface
{
    public function index( $request, $clubId );
    public function create(array $data, $file);
    public function getNewsById( $id, $userId );
    public function update($id, array $data, $file);
    public function delete($id);
    public function getClubNewsExtras( $request );
}
