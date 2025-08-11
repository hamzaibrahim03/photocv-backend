<?php

namespace App\Repositories\ClubAdmin;

interface PagesRepositoryInterface
{
    public function all( $request );
    public function create(array $data, $file);
    public function show( $id );
    public function update($id, array $data, $file);
    public function delete($id);
    public function getPagesExtras( $request );
}
