<?php

namespace App\Repositories\Member;

interface MemberCompetitionRepositoryInterface
{
    public function joinCompetition($data);
    public function getJoinedCompetition();
    public function submitCompetitionEntry($data);
    public function getCompetitionEntries($compId);
    public function updateCompetitionEntry($entryId, $data);
    public function deleteCompetitionEntry($entryId);
}
