<?php

namespace App\Repositories\ClubPublic;

interface PublicClubRepositoryInterface
{
    public function getClubHomeData($request);
    public function getClubEventData($request);
    public function getClubSingleEventData($request);
    public function getClubCompetitionData($request);
    public function getClubSingleCompetitionData($request);
    public function getClubGalleries($request);
    public function getClubNewsData($request);
    public function getClubSingleNewsData($request);
}
