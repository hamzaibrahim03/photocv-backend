<?php

namespace App\Repositories;

use App\Models\MemberNotice;
use App\Traits\UtilityTrait;
use App\Http\Responses\NoticeResponse;

class NoticeRepository implements NoticeRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $notices = $this->getAllNoticeData($request);
            return NoticeResponse::success('Notices retrieved successfully.', $notices);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function show( $id )
    {
        try {
            $notice = MemberNotice::findOrFail($id);
            if (!$notice) {
                return NoticeResponse::error('Notice not found.', 404);
            }

            return NoticeResponse::success('Notice retrieved successfully.', $notice);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(array $data, $images = null, $document = null)
    {
        try {
            $notice = MemberNotice::create($data);
            
            return NoticeResponse::success('Notice created successfully.', $notice, 201);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function update($id, array $data, $images = null, $document = null)
    {
        try {
            $notice = MemberNotice::findOrFail($id);
            if (!$notice) {
                return NoticeResponse::error('Notice not found.', 404);
            }

            $notice->update($data);

            return NoticeResponse::success('Notice updated successfully.', $notice);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function delete($id)
    {
        try {
            $notice = MemberNotice::findOrFail($id);

            if (!$notice) {
                return NoticeResponse::error('Notice not found or already deleted.', 404);
            }

            $notice->delete();
            return NoticeResponse::success('Notice deleted successfully.');
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

}
