<?php

namespace App\Repositories\Member;

interface MemberNoteRepositoryInterface
{
    public function index( $request );
    public function store( $data );
    public function show( $id );
    public function update( $data, $id );
    public function delete( $id );
}
