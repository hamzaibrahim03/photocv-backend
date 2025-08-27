<?php

namespace App\Repositories\ClubAdmin;

interface EventRepositoryInterface
{
    public function all( $request );
    public function create(array $data, $files);
    public function show( $id );
    public function update($id, array $data, $files);
    public function delete($id);
    public function getEventExtras($request);
    public function getUpcomingEventsForAllClubs($userId);
}
