<?php

namespace App\Repositories\Member;

use App\Traits\DataTables\MemberDataTableTrait;
use App\Http\Responses\MemberResponse;
use App\Models\MemberPracticeLog;
use Illuminate\Support\Facades\Storage;

class MemberPracticeLogRepository implements MemberPracticeLogRepositoryInterface
{
    use MemberDataTableTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * Method to delete note
     * @param mixed $id
     */
    public function delete($id)
    {
        try {
            $log = MemberPracticeLog::where('id', $id)
                ->where('member_id', auth()->id())
                ->firstOrFail();

            Storage::disk('public')->delete($log->file);
            $log->delete();

            return MemberResponse::success('Data deleted successfully.', $log);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
