<?php
namespace App\Services\Member;

use App\Repositories\Member\MemberClassLogRepositoryInterface;

class MemberClassLogService
{
    private $memberClassLogRepository;

    public function __construct(MemberClassLogRepositoryInterface $memberClassLogRepository)
    {
        $this->memberClassLogRepository = $memberClassLogRepository;
    }

    /**
     * Get all class logs with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        return $this->memberClassLogRepository->index( $request );
    }

    /**
     * Method to store class log
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->memberClassLogRepository->store($data);
    }

    /**
     * Method to show single class log
     * @param mixed $data
     */
    public function show($id)
    {
        return $this->memberClassLogRepository->show($id);
    }

    /**
     * Method to update class log
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->memberClassLogRepository->update($data, $id);
    }

    /**
     * Method to delete class log
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->memberClassLogRepository->delete($id);
    }
}
