<?php

namespace App\Repositories\ClubAdmin;

interface CompetitionResultRepositoryInterface
{
    public function index( $request, $user );
    public function getCompetitionEntriesWithScores( $id, $request, $user );
    public function assignPositionsAndPublish( $data, $competitionId );
    public function getAllPublishedResults($clubId = null);

    public function getAllPublishedResultsForHome($clubId = null);
    
}
