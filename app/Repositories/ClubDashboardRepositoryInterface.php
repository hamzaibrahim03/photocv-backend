<?php

namespace App\Repositories;

interface ClubDashboardRepositoryInterface
{
    public function getAuthUserDetails();
    public function getLatestEvents(int $clubId, int $limit = 3);
    public function getLatestCompetitions(int $clubId, int $limit = 3);
    public function getLatestPages(int $clubId, int $limit = 6);
    public function getLatestNotices(int $clubId, int $limit = 3);
    public function getLatestClubNews(int $clubId, int $limit = 3);
    public function getLatestMembers(int $clubId, int $limit = 3);
    public function getMembersGallerries(int $clubId, int $limit = 10);
    public function getClubGallerries(int $clubId, int $limit = 10);
    public function getCurrentMonthActivities(int $clubId);
}