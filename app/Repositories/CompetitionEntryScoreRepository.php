<?php

namespace App\Repositories;

use App\Models\CompetitionEntryScore;
use App\Models\CompetitionMembersEntry;

class CompetitionEntryScoreRepository implements CompetitionEntryScoreRepositoryInterface
{
    public function getJudgeScoresForCompetition($competitionId, $judgeId)
    {
        return CompetitionMembersEntry::whereHas('competitionMember', function($q) use ($competitionId) {
                $q->where('comp_id', $competitionId);
            })
            ->with([
                'competitionMember.member',
                'scores' => function($q) use ($judgeId) {
                    $q->where('judge_id', $judgeId);
                }
            ])
            ->get();
    }

    public function saveOrUpdateScore($entryId, $judgeId, array $data)
    {
        return CompetitionEntryScore::updateOrCreate(
            ['entry_id' => $entryId, 'judge_id' => $judgeId],
            [
                'score' => $data['score'],
                'comment' => $data['comment'] ?? null
            ]
        );
    }
}
