<?php

namespace App\Repositories\ClubPublic;

interface PublicClubRepositoryInterface
{
    public function getClubHomeData($request);
    public function getClubEventData($request);
    public function getClubSingleEventData($request, $username, $id);
    public function getClubCompetitionData($request);
    public function getClubSingleCompetitionData($request, $username, $id);
    public function getClubGalleries($request);
    public function clubGallery($request, $username, $galleryId);
    public function getRandomClubGalleries($request);
    public function getRandomMemberGalleries($request);
    public function memberGalleries($request, $username, $memberId);
    public function getClubNewsData($request);
    public function getClubSingleNewsData($request, $username, $id);
    public function aboutUs($request);
    public function getClubNoticesData($request);
    public function getClubSingleNotice($request, $username, $id);
}
