<?php

namespace App\Repositories\ClubAdmin;

interface PagesRepositoryInterface
{
    public function getLatestPagesByLimit(int $clubId, int $limit = 6);
    public function all( $request );
    public function create(array $data, $file);
    public function show( $id );
    public function update($id, array $data, $file);
    public function delete($id);
    public function getPagesExtras( $request );
}
