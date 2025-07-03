<?php

namespace App\Repositories;

interface MemberAdminRepositoryInterface
{
    public function allEvents( $request );
    public function memberSingleEvent($id);

}
