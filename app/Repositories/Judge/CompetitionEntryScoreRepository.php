<?php

namespace App\Repositories\Judge;

use App\Models\CompetitionEntryScore;
use App\Http\Responses\GenericResponse;
use App\Models\CompetitionMembersEntry;
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
            // Self-heals any award positions that were computed with an
            // older ranking rule (or otherwise drifted from the scores on
            // record) before returning the list.
            $this->recomputeAwardPositions($competitionId, $judgeId);

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
        try {
            // Load entry with related competition and judges
            $entry = CompetitionMembersEntry::with('competitionMember.competition.judges')->findOrFail($entryId);

            $competition = $entry->competitionMember?->competition;

            // Check if judge is assigned
            if (! $competition || ! $competition->judges->contains('id', $judgeId)) {
                return GenericResponse::error(
                    'You are not a judge or not assigned to this competition.',
                    403
                );
            }

            // Update or create score; keep the existing bookmark unless one is sent.
            // position is not set here - it's computed from the ranked scores below.
            $values = [
                'score' => $data['score'] ?? null,
                'comment' => $data['comment'] ?? null,
            ];

            if (array_key_exists('is_bookmarked', $data)) {
                $values['is_bookmarked'] = (bool) $data['is_bookmarked'];
            }

            $score = CompetitionEntryScore::updateOrCreate(
                ['entry_id' => $entryId, 'judge_id' => $judgeId],
                $values
            );

            $this->recomputeAwardPositions($competition->id, $judgeId);

            return GenericResponse::success(
                'Score saved successfully.',
                $score->fresh()
            );
        } catch (\Exception $e) {
            return GenericResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * Ranks every entry this judge has scored in the competition by score
     * (highest first) and assigns the award position automatically: ranks
     * 1-9 get numeric positions, ranks 10-12 get C / H / I, anything beyond
     * rank 12 gets no award. Mirrors the position scheme already used in
     * SingleMemberEntries.jsx / RytonSingleResults.jsx / CompetitionSinglePublic.jsx.
     *
     * Entries with an equal score share the same award position (dense
     * ranking): if two entries tie for 1st, the next distinct score is
     * ranked 2nd, not 3rd - no rank numbers are skipped over a tie.
     */
    public function recomputeAwardPositions(int $competitionId, int $judgeId): void
    {
        $rankLabels = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'C', 'H', 'I'];

        $scores = CompetitionEntryScore::where('judge_id', $judgeId)
            ->whereNotNull('score')
            ->whereHas('entry.competitionMember', fn ($q) => $q->where('comp_id', $competitionId))
            ->orderByDesc('score')
            ->orderBy('entry_id')
            ->get();

        $rank = 0;
        $previousScore = null;
        foreach ($scores as $score) {
            if ($previousScore === null || $score->score !== $previousScore) {
                $rank++;
            }
            $previousScore = $score->score;

            $position = $rankLabels[$rank - 1] ?? null;
            if ($score->position !== $position) {
                $score->update(['position' => $position]);
            }
        }
    }

}
