<?php
namespace App\Services\Member;

use App\Repositories\Member\PlannedLocationRepositoryInterface;
use App\Http\Responses\MemberResponse;
use App\Http\Resources\PlannedLocationResource;

class PlannedLocationService
{
    private $plannedLocationRepository;

    public function __construct(PlannedLocationRepositoryInterface $plannedLocationRepository)
    {
        $this->plannedLocationRepository = $plannedLocationRepository;
    }

    /**
     * Get all planned locations of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        return $this->plannedLocationRepository->all( $request );
    }

    /**
     * Method to store planned location
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->plannedLocationRepository->create($data);
    }

    /**
     * Method to show single planned location
     * @param mixed $data
     */
    public function show($id)
    {
        $location = $this->plannedLocationRepository->find($id);
        return MemberResponse::success('Reterived successfully.', new PlannedLocationResource($location), 201);
    }

    /**
     * Method to update planned location
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->plannedLocationRepository->update($id, $data);
    }

    /**
     * Method to delete planned location
     * @param mixed $id
     */
    public function delete($id)
    {
        return $this->plannedLocationRepository->delete($id);
    }

}
