<?php

namespace App\Repositories\ClubAdmin;

interface ClubSettingsRepositoryInterface
{
    public function getAllClubSettings( );
    public function save(array $data, $logo, $clubBanner, $id);
}
