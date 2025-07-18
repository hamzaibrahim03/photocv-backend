<?php
namespace App\Services;

use App\Repositories\MemberPracticeLogRepositoryInterface;

class MemberPracticeLogService
{
    private $memberPracticeLogRepository;

    public function __construct(MemberPracticeLogRepositoryInterface $memberPracticeLogRepository)
    {
        $this->memberPracticeLogRepository = $memberPracticeLogRepository;
    }

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        return $this->memberPracticeLogRepository->index( $request );
    }

    /**
     * Method to store note
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->memberPracticeLogRepository->store($data);
    }

    /**
     * Method to show single note
     * @param mixed $data
     */
    public function show($id)
    {
        return $this->memberPracticeLogRepository->show($id);
    }

    /**
     * Method to update note
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->memberPracticeLogRepository->update($data, $id);
    }

    /**
     * Method to delete note
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->memberPracticeLogRepository->delete($id);
    }
}
