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

    public function create(array $data)
    {
        return Competition::create($data);
    }

    public function update($id, array $data)
    {
        $competition = Competition::findOrFail($id);
        $competition->update($data);

        return $competition;
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
