<?php
namespace App\Services\Member;

use App\Repositories\Member\GearLibraryRepositoryInterface;
use App\Http\Responses\MemberResponse;
use App\Http\Resources\GearLibraryResource;

class GearLibraryService
{
    private $gearLibraryRepository;

    public function __construct(GearLibraryRepositoryInterface $gearLibraryRepository)
    {
        $this->gearLibraryRepository = $gearLibraryRepository;
    }

    /**
     * Get all gear libraries of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($request)
    {
        return $this->gearLibraryRepository->all($request);
    }

    /**
     * Method to store gear libraries
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->gearLibraryRepository->create($data);
    }

    /**
     * Method to show single gear libraries
     * @param mixed $id
     */
    public function show($id)
    {
        $record = $this->gearLibraryRepository->find($id);
        return MemberResponse::success('Retrieved successfully.', new GearLibraryResource($record));
    }

    /**
     * Method to update gear libraries
     * @param mixed $data
     * @param mixed $id
     */
    public function update($data, $id)
    {
        return $this->gearLibraryRepository->update($id, $data);
    }

    /**
     * Method to delete gear libraries
     * @param mixed $id
     */
    public function delete($id)
    {
        return $this->gearLibraryRepository->delete($id);
    }
}
