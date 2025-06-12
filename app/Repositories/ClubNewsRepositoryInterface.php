<?php

namespace App\Repositories;

interface ClubNewsRepositoryInterface
{
    public function all( $request );
    public function create(array $data, $file);
    public function show( $id );
    public function update($id, array $data, $file);
    public function delete($id);
    public function getClubNewsExtras( $request );
}
