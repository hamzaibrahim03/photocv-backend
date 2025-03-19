<?php

namespace App\Repositories;

use App\Models\Competition;
use App\Traits\UtilityTrait;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        return $this->getAllCompetitionData($request);
    }

    public function show( $id )
    {
        $competition = Competition::findOrFail($id);
        $competition->load('images');

        return $competition;
    }

    public function create(array $data, $files = null)
    {
        return Competition::create($data);
    }

    public function update($id, array $data, $files = null)
    {
        $competition = Competition::findOrFail($id);
        return $competition->update($data);
    }

    public function delete($id)
    {
        $competition = Competition::findOrFail($id);
        if ( $competition ) {
            $competition->delete();
            return 'Competition Deleted Succesfully';
        }
        return $competition;
    }

}
