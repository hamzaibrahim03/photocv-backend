<?php

namespace App\Repositories\ClubAdmin;

interface ClubSettingsRepositoryInterface
{
    public function all( );
    public function save(array $data, $logo, $clubBanner, $id);
}
