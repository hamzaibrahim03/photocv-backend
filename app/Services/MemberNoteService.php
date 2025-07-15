<?php
namespace App\Services;

use App\Repositories\MemberNoteRepositoryInterface;

class MemberNoteService
{
    private $memberNoteRepository;

    public function __construct(MemberNoteRepositoryInterface $memberNoteRepository)
    {
        $this->memberNoteRepository = $memberNoteRepository;
    }

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allMemberNotes( $request )
    {
        return $this->memberNoteRepository->index( $request );
    }

    /**
     * Method to store note
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->memberNoteRepository->store($data);
    }

    /**
     * Method to show single note
     * @param mixed $data
     */
    public function show($id)
    {
        return $this->memberNoteRepository->show($id);
    }

    /**
     * Method to update note
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->memberNoteRepository->update($data, $id);
    }

    /**
     * Method to delete note
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->memberNoteRepository->delete($id);
    }
}
