<?php

namespace App\Repositories\ClubAdmin;

interface CompetitionRepositoryInterface
{
    public function all( $request );
    public function create(array $data);
    public function show( $id );
    public function update($id, array $data);
    public function delete($id);
    public function getCompetitionExtras($request);
    public function getCompetitionResults();
    public function getLatestCompetitionsByLimit(int $clubId, int $limit = 3);
}
