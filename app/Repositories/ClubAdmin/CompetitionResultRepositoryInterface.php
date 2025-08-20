<?php

namespace App\Repositories\ClubAdmin;

interface CompetitionResultRepositoryInterface
{
    public function index( $request );
    public function show( $id, $request );
    public function assignPositionsAndPublish( $data, $competitionId );
    public function getAllPublishedResults($clubId = null);
    
}
