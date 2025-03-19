<?php

namespace App\Repositories;

interface CompetitionRepositoryInterface
{
    public function all( $request );
    public function create(array $data, $files);
    public function show( $id );
    public function update($id, array $data, $files);
    public function delete($id);
}
