<?php

namespace App\Repositories;

use App\Models\Competition;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $competitions = $this->getAllIndexData($request, Competition::all());
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
            $competition->update($data);

            if (!$competition) {
                return CompetitionResponse::error('Competition not found.', 404);
            }

            return CompetitionResponse::success('Competition updated successfully.', $competition);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function delete($id)
    {
        try {
            $competition = Competition::findOrFail($id);

            if (!$competition) {
                return CompetitionResponse::error('Competition not found or already deleted.', 404);
            }

            $competition->delete();
            return CompetitionResponse::success('Competition deleted successfully.');
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

}
