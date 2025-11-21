<?php
namespace App\Services\ClubPublic;

use App\Repositories\ClubPublic\PublicClubRepositoryInterface;

class PublicClubService
{
    private $publicClubRepository;

    public function __construct(PublicClubRepositoryInterface $publicClubRepository)
    {
        $this->publicClubRepository = $publicClubRepository;
    }

    /**
     * Method to get club public home data
     * @param mixed $request
     */
    public function getClubHomeData($request)
    {
        return $this->publicClubRepository->getClubHomeData($request);
    }

    /**
     * Method to get club public event data
     * @param mixed $request
     */
    public function getClubEventData($request)
    {
        return $this->publicClubRepository->getClubEventData($request);
    }

    /**
     * Method to get club public single event data
     * @param mixed $request
     */
    public function getClubSingleEventData($request)
    {
        return $this->publicClubRepository->getClubSingleEventData($request);
    }

    /**
     * Method to get club public upcoming competition data
     * @param mixed $request
     */
    public function getClubCompetitionData($request)
    {
        return $this->publicClubRepository->getClubCompetitionData($request);
    }

    /**
     * Method to get club public single competition data
     * @param mixed $request
     */
    public function getClubSingleCompetitionData($request)
    {
        return $this->publicClubRepository->getClubSingleCompetitionData($request);
    }

    /**
     * Method to get club public galleries
     * @param mixed $request
     */
    public function getClubGalleries($request)
    {
        return $this->publicClubRepository->getClubGalleries($request);
    }

    /**
     * Method to get single member galleries
     * @param mixed $request
     * @param mixed $username
     * @param mixed $memberId
     */
    public function memberGalleries($request, $username, $memberId)
    {
        return $this->publicClubRepository->memberGalleries($request, $username, $memberId);
    }

    /**
     * Method to get sigle gallery information
     * @param mixed $request
     * @param mixed $username
     * @param mixed $galleryId
     */
    public function clubGallery($request, $username, $galleryId)
    {
        return $this->publicClubRepository->clubGallery($request, $username, $galleryId);
    }

    /**
     * Method to get random club galleries
     * @param mixed $request
     */
    public function getRandomClubGalleries($request)
    {
        return $this->publicClubRepository->getRandomClubGalleries($request);
    }

    /**
     * Method to get random member galleries
     * @param mixed $request
     */
    public function getRandomMemberGalleries($request)
    {
        return $this->publicClubRepository->getRandomMemberGalleries($request);
    }

    /**
     * Method to get club public news data
     * @param mixed $request
     */
    public function getClubNewsData($request)
    {
        return $this->publicClubRepository->getClubNewsData($request);
    }

    /**
     * Method to get club public single news data
     * @param mixed $request
     */
    public function getClubSingleNewsData($request)
    {
        return $this->publicClubRepository->getClubSingleNewsData($request);
    }

    /**
     * Get about us data for the public page
     * @param mixed $request
     */
    public function aboutUs($request)
    {
        return $this->publicClubRepository->aboutUs($request);
    }

    /**
     * Method to get club public notices data
     * @param mixed $request
     */
    public function getClubNoticesData($request)
    {
        return $this->publicClubRepository->getClubNoticesData($request);
    }

    /**
     * Method to get club public single notice data
     * @param mixed $request
     * @param mixed $username
     * @param mixed $id
     */
    public function getClubSingleNotice($request, $username, $id)
    {
        return $this->publicClubRepository->getClubSingleNotice($request, $username, $id);
    }

}
