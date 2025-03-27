<?php

namespace App\Repositories;

use App\Models\MemberNotice;
use App\Models\MemberNoticeFile;
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

            $notice->load('files');

            return NoticeResponse::success('Notice retrieved successfully.', $notice);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(array $data, $images = [], $documents = [])
    {
        try {
            // Create the notice
            $notice = MemberNotice::create($data);

            // Handle Image Uploads
            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    if ($image) {
                        $imagePath = $image->store('notices/images', 'public'); 
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $image->getClientOriginalName(),
                            'file_type' => 'image',
                            'file_path' => $imagePath,
                        ]);
                    }
                }
            }

            // Handle Document Uploads
            if (!empty($documents) && is_array($documents)) {
                foreach ($documents as $document) {
                    if ($document) {
                        $documentPath = $document->store('notices/documents', 'public');
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $document->getClientOriginalName(),
                            'file_type' => 'document',
                            'file_path' => $documentPath,
                        ]);
                    }
                }
            }

            return NoticeResponse::success('Notice created successfully.', $notice, 201);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function update($id, array $data, $images = [], $documents = [])
    {
        try {
            $notice = MemberNotice::findOrFail($id);

            // Update notice details
            $notice->update($data);

            // Handle Image Uploads
            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    if ($image) {
                        $imagePath = $image->store('notices/images', 'public'); 
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $image->getClientOriginalName(),
                            'file_type' => 'image',
                            'file_path' => $imagePath,
                        ]);
                    }
                }
            }

            // Handle Document Uploads
            if (!empty($documents) && is_array($documents)) {
                foreach ($documents as $document) {
                    if ($document) {
                        $documentPath = $document->store('notices/documents', 'public');
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $document->getClientOriginalName(),
                            'file_type' => 'document',
                            'file_path' => $documentPath,
                        ]);
                    }
                }
            }

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
