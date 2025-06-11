<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\Competition;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    use UtilityTrait;

    public function all($request)
    {
        try {
            $clubId = auth()->user()->club->id;
            $query = Competition::where('club_id', $clubId); // filters competitions by user's club
            $competitions = $this->getAllIndexData($request, $query);
            return CompetitionResponse::success('Competitions retrieved successfully.', $competitions);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function show( $id )
    {
        try {
            $competition = Competition::findOrFail($id);

            if (!$competition) {
                return CompetitionResponse::error('Competition not found.', 404);
            }

            return CompetitionResponse::success('Competition retrieved successfully.', $competition);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(array $data)
    {
        try {
            $club = Club::where('user_id', auth()->id())->first();
            if ($club) {
                $data['club_id'] = $club->id;
            }

            $competition = Competition::create($data);
            return CompetitionResponse::success('Competition created successfully.', $competition, 201);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function update($id, array $data)
    {
        try {
            $competition = Competition::findOrFail($id);

            //Ensure the authenticated user's club owns this competition
            $club = Club::where('user_id', auth()->id())->first();
            if ($club && $competition->club_id !== $club->id) {
                return CompetitionResponse::error('Unauthorized to update this competition.', 403);
            }

            $competition->update($data);

            return CompetitionResponse::success('Competition updated successfully.', $competition);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function delete($id)
    {
        try {
            $competition = Competition::findOrFail($id);

            // Restrict deletion to the competition's owning club
            $club = Club::where('user_id', auth()->id())->first();
            if ($club && $competition->club_id !== $club->id) {
                return CompetitionResponse::error('Unauthorized to delete this competition.', 403);
            }

            $competition->delete();

            return CompetitionResponse::success('Competition deleted successfully.');
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


}
