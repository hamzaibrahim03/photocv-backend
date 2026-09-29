<?php
namespace App\Services\Member;

use App\Repositories\Member\GearWishlistRepositoryInterface;
use App\Http\Responses\MemberResponse;
use App\Http\Resources\GearWishlistResource;

class GearWishlistService
{
    private $gearWishlistRepository;

    public function __construct(GearWishlistRepositoryInterface $gearWishlistRepository)
    {
        $this->gearWishlistRepository = $gearWishlistRepository;
    }

    /**
     * Get all wishlist items of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($request)
    {
        return $this->gearWishlistRepository->all($request);
    }

    /**
     * Method to store wishlist items
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->gearWishlistRepository->create($data);
    }

    /**
     * Method to show single wishlist items
     * @param mixed $id
     */
    public function show($id)
    {
        $record = $this->gearWishlistRepository->find($id);
        return MemberResponse::success('Retrieved successfully.', new GearWishlistResource($record));
    }

    /**
     * Method to update wishlist items
     * @param mixed $data
     * @param mixed $id
     */
    public function update($data, $id)
    {
        return $this->gearWishlistRepository->update($id, $data);
    }

    /**
     * Method to delete wishlist items
     * @param mixed $id
     */
    public function delete($id)
    {
        return $this->gearWishlistRepository->delete($id);
    }
}
