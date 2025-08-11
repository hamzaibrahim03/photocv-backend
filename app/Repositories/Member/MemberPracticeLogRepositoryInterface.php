<?php

namespace App\Repositories\Member;

interface MemberPracticeLogRepositoryInterface
{
    public function index( $request );
    public function store( $data );
    public function delete( $id );
}
