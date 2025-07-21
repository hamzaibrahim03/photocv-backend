<?php

namespace App\Repositories;

interface ClubGalleryRepositoryInterface
{
    public function getClubGalleries($clubAdminId);
    public function createClubGallery($data);
    public function getClubGallery($id);
}
