<?php
namespace App\Services;

use App\Repositories\MemberInterestBrandRepositoryInterface;

class MemberInterestBrandService
{
    private $memberInterestBrandRepository;

    public function __construct(MemberInterestBrandRepositoryInterface $memberInterestBrandRepository)
    {
        $this->memberInterestBrandRepository = $memberInterestBrandRepository;
    }

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        return $this->memberInterestBrandRepository->index( $request );
    }

    /**
     * Method to store interest/brand
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->memberInterestBrandRepository->store($data);
    }

    /**
     * Method to show single interest/brand
     * @param mixed $data
     */
    public function show($id)
    {
        return $this->memberInterestBrandRepository->show($id);
    }

    /**
     * Method to update interest/brand
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->memberInterestBrandRepository->update($data, $id);
    }

    /**
     * Method to delete interest/brand
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->memberInterestBrandRepository->delete($id);
    }
}
