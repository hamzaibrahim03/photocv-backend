<?php

namespace App\Repositories;

interface MemberAdminRepositoryInterface
{
    public function allEvents( $request );
    public function memberSingleEvent($id);
    public function allCompetitions($request);
    public function memberSingleCompetition($id);
    public function getMemberNotices();

    public function addMemberNotes($data);
}
