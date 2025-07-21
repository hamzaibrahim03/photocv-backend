<?php
namespace App\Services;

use App\Repositories\ClubGalleryRepositoryInterface;

class ClubGalleryService
{
    private $clubGalleryRepository;

    public function __construct(ClubGalleryRepositoryInterface $clubGalleryRepository)
    {
        $this->clubGalleryRepository = $clubGalleryRepository;
    }

    /**
     * Method to get club galleries
     * @param mixed $data
     */
    public function getClubGalleries($clubAdminId)
    {
        return $this->clubGalleryRepository->getClubGalleries($clubAdminId);
    }

    /**
     * Method to get club gallery information
     * @param mixed $id
     */
    public function getClubGallery($id)
    {
        return $this->clubGalleryRepository->getClubGallery($id);
    }

    /**
     * Method to create club gallery
     * @param mixed $data
     * @return void
     */
    public function createClubGallery($data)
    {
        return $this->clubGalleryRepository->createClubGallery($data);
    }

}
