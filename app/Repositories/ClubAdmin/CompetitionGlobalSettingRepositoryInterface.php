<?php

namespace App\Repositories\ClubAdmin;

interface CompetitionGlobalSettingRepositoryInterface
{
    public function getFirst( );
    public function createOrUpdate(array $data);
}
