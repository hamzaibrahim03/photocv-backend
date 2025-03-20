<?php

namespace App\Repositories;

interface CompetitionRepositoryInterface
{
    public function all( $request );
    public function create(array $data);
    public function show( $id );
    public function update($id, array $data);
    public function delete($id);
}
