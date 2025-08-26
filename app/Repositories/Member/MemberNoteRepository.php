<?php

namespace App\Repositories\Member;

use App\Http\Responses\MemberResponse;
use App\Traits\DataTables\NoticeDataTableTrait;
use App\Models\MemberNote;

class MemberNoteRepository implements MemberNoteRepositoryInterface
{
    use NoticeDataTableTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function index( $request )
    {
        try {
            $memberNotes = $this->getAllMemberNotes($request, auth()->user()->id);
            return MemberResponse::success('MemberNotes retrieved successfully.', $memberNotes);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to store note
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store($data)
    {
        try {
            $data['member_id'] = auth()->user()->id;
            $note = MemberNote::create($data);
            return MemberResponse::success('Note created successfully.', $note);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to show single note
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $note = MemberNote::with('member')
            ->where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();
            return MemberResponse::success('Note retrieved successfully.', $note);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to update note
     * @param mixed $data
     * @param mixed $id
     */
    public function update($data, $id)
    {
        try {
            $note = MemberNote::where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();

            $note->update(array_intersect_key($data, array_flip(['title', 'description', 'type'])));

            return MemberResponse::success('Note updated successfully.', $note);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to delete note
     * @param mixed $id
     */
    public function delete($id)
    {
        try {
            $note = MemberNote::where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();

            $note->delete();

            return MemberResponse::success('Note deleted successfully.', $note);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
