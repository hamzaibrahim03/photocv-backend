<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Competition;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;

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
            if ($user->hasRole('club_admin')) { // adjust this check as per your roles setup
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
                'competitionTheme'
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


}
