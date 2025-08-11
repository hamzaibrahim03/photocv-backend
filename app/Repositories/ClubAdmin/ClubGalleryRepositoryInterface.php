<?php

namespace App\Repositories\ClubAdmin;

interface ClubGalleryRepositoryInterface
{
    public function getClubGalleries($clubAdminId);
    public function createClubGallery($data);
    public function getClubGallery($id);
}
