<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Competition;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;
use Illuminate\Support\Facades\DB;
use App\Models\CompetitionMembersEntry;

class CompetitionResultRepository implements CompetitionResultRepositoryInterface
{
    use UtilityTrait;

    public function index($request)
    {

        try {
            $user = auth()->user();

            // Base query with eager-loaded relationships
            $query = Competition::with([
                'judgingType',
                'competitionType',
                'resultMethod',
                'votingMethod',
                'competitionCategory',
                'competitionTheme'
            ]);

            // If user is a club admin, filter by their club
            if ($user->hasRole('club_admin')) {
                $clubId = $user->club->id ?? null;
                if ($clubId) {
                    $query->where('club_id', $clubId);
                } else {
                    return CompetitionResponse::error('Club not found for this admin.', 404);
                }
            }

            // Get paginated/searchable DataTable results
            $competitions = $this->getAllCompetitionResults($request, $query, 'name');

            return CompetitionResponse::success('Competitions retrieved successfully.', $competitions);

        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function show($id, $request)
    {
        try {
            $user = auth()->user();

            $query = Competition::with([
                'judgingType',
                'competitionType',
                'resultMethod',
                'votingMethod',
                'competitionCategory',
                'competitionTheme',
                'judges'
            ])->where('id', $id);

            if ($user->hasRole('club_admin')) {
                $clubId = $user->club->id ?? null;
                if ($clubId) {
                    $query->where('club_id', $clubId);
                } else {
                    return CompetitionResponse::error('Club not found for this admin.', 404);
                }
            }

            $competitionEntries = $this->getCompetitionEntryData($request, $query, 'name');
            return CompetitionResponse::success('Competition entries successfully.', $competitionEntries);

        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function assignPositionsAndPublish($data, $competitionId)
    {
        DB::beginTransaction();
        try {
            foreach ($data['entries'] as $entryData) {
                $entry = CompetitionMembersEntry::where('id', $entryData['entry_id'])
                    ->whereHas('competitionMember', function ($q) use ($competitionId) {
                        $q->where('comp_id', $competitionId);
                    })
                    ->first();

                if ($entry) {
                    // If total_score comes from payload use it, otherwise recalc from judge scores
                    $totalScore = $entryData['total_score'] ?? $entry->scores()->sum('score');

                    $entry->update([
                        'position'     => $entryData['position'] ?? null,
                        'total_score'  => $totalScore,
                        'is_published' => $data['is_published'] ?? false,
                    ]);
                }
            }

            DB::commit();
            return CompetitionResponse::success('Results successfully updated and published.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
