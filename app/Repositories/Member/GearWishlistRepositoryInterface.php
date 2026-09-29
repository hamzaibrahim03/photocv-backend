<?php

namespace App\Repositories\Member;

interface GearWishlistRepositoryInterface
{
    public function all($request);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
