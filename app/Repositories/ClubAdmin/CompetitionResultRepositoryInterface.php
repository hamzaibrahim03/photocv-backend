<?php

namespace App\Repositories\ClubAdmin;

interface CompetitionResultRepositoryInterface
{
    public function index( $request );
    public function show( $id, $request );
    
}
