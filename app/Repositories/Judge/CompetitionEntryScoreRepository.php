<?php

namespace App\Repositories\Judge;

use App\Models\CompetitionEntryScore;
use App\Http\Responses\GenericResponse;
use App\Traits\DataTables\CompetitionDataTableTrait;

class CompetitionEntryScoreRepository implements CompetitionEntryScoreRepositoryInterface
{
    use CompetitionDataTableTrait;

    /**
     * Get judge scores for a specific competition.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $competitionId
     * @param  int  $judgeId
     */
    public function getJudgeScoresForCompetition($request, $competitionId, $judgeId)
    {
        try {
            return $this->getJudgeScoresForCompetitionDatatable($request, $competitionId, $judgeId, );
        } catch (\Exception $e) {
            return GenericResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Save or update a judge's score for a competition entry.
     *
     * @param  int  $entryId
     * @param  int  $judgeId
     * @param  array  $data
     */
    public function saveOrUpdateScore($entryId, $judgeId, array $data)
    {
        return CompetitionEntryScore::updateOrCreate(
            ['entry_id' => $entryId, 'judge_id' => $judgeId],
            [
                'score' => $data['score'] ?? null,
                'comment' => $data['comment'] ?? null,
                'position' => $data['position'] ?? null, // 1,2,3
                'is_bookmarked' => $data['is_bookmarked'] ?? false,
            ]
        );
    }
}
