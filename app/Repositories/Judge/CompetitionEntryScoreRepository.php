<?php

namespace App\Repositories\Judge;

use App\Models\Competition;
use App\Models\CompetitionEntryScore;
use App\Models\CompetitionMembersEntry;

class CompetitionEntryScoreRepository implements CompetitionEntryScoreRepositoryInterface
{
    public function getJudgeScoresForCompetition($competitionId, $judgeId)
    {
        // Load competition with judges
        $competition = Competition::with('judges')->findOrFail($competitionId);

        // Check if this judge is assigned
        if (! $competition->judges->contains('id', $judgeId)) {
            return [
                'success' => false,
                'message' => 'You are not a judge or not assigned to this competition.'
            ];
        }

        // Fetch entries with member & scores via relationships
        $entries = CompetitionMembersEntry::whereHas('competitionMember', function ($q) use ($competitionId) {
                $q->where('comp_id', $competitionId);
            })
            ->with([
                'competitionMember.member',
                'scores' => function ($q) use ($judgeId) {
                    $q->where('judge_id', $judgeId);
                }
            ])
            ->get();

        // Use relationship instead of raw query for position counts
        $positionCounts = CompetitionEntryScore::with(['entry.competitionMember'])
            ->whereHas('entry.competitionMember', function ($q) use ($competitionId) {
                $q->where('comp_id', $competitionId);
            })
            ->where('judge_id', $judgeId)
            ->get()
            ->groupBy('position')
            ->map->count();

        return [
            'success' => true,
            'entries' => $entries,
            'progress' => [
                'scored' => $entries->filter(fn($e) => $e->scores->isNotEmpty())->count(),
                'total'  => $entries->count(),
            ],
            'positions' => [
                'first'  => $positionCounts[1] ?? 0,
                'second' => $positionCounts[2] ?? 0,
                'third'  => $positionCounts[3] ?? 0,
            ]
        ];
    }

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
