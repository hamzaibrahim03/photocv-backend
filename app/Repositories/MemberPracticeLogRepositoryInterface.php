<?php

namespace App\Repositories;

interface MemberPracticeLogRepositoryInterface
{
    public function index( $request );
    public function store( $data );
    public function delete( $id );
}
