<?php

namespace App\Repositories;

interface ClubSettingsRepositoryInterface
{
    public function all( );
    public function save(array $data, $logo, $clubBanner, $id);
}
