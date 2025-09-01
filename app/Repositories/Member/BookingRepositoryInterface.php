<?php

namespace App\Repositories\Member;

interface BookingRepositoryInterface
{
    public function index( $request );
    public function create( $data );
    public function find( $id );
    public function update( $data, $id );
    public function delete( $id );
}
