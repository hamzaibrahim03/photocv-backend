<?php

namespace App\Repositories;

use App\Traits\UtilityTrait;
use App\Http\Responses\MemberResponse;
use App\Models\MemberPracticeLog;

class MemberPracticeLogRepository implements MemberPracticeLogRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        try {
            $type = $request->get('type'); // 'image', 'document', or 'video'

            if (!in_array($type, ['image', 'document', 'video'])) {
                return MemberResponse::error('Invalid type provided.', 400);
            }

            return MemberResponse::success('Data retrieved successfully.', $this->getDataTableResponse($request, $type));
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to store practice log
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store($data)
    {
        try {
            $data['member_id'] = auth()->user()->id;
            $file = $data['file'];
            $ext = strtolower($file->getClientOriginalExtension());

            $type = match (true) {
                in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => 'image',
                in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']) => 'document',
                in_array($ext, ['mp4', 'mov', 'avi', 'webm']) => 'video',
                default => 'unknown',
            };

            if ($type === 'unknown') {
                return response()->json(['message' => 'Unsupported file type.'], 422);
            }

            $path = $file->store('uploads/practice_logs', 'public');

            $data['file'] = $path;
            $data['type'] = $type;

            $log = MemberPracticeLog::create($data);

            return MemberResponse::success('Data stored successfully.', $log);
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
     * @return void
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
     * @return void
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
