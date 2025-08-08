<?php

namespace App\Repositories\Member;

interface MemberCompetitionRepositoryInterface
{
    public function joinCompetition($data);
    public function getJoinedCompetition();
    public function submitCompetitionEntry($data);
}
