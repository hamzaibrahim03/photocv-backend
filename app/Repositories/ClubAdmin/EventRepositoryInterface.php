<?php

namespace App\Repositories\ClubAdmin;

interface EventRepositoryInterface
{
    public function all( $request, $userId );
    public function create(array $data, $files);
    public function getEventById( $id, $userId );
    public function update($id, array $data, $files);
    public function delete($id);
    public function getEventExtras($request);
    public function getUpcomingEventsForAllClubs($userId);
    public function getTotalEvents($clubId);
    public function getUpcomingEventRemainingDays($clubId);
}
