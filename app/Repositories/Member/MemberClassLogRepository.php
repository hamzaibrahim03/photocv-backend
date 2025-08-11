<?php

namespace App\Repositories\Member;

use App\Traits\UtilityTrait;
use App\Http\Responses\MemberResponse;
use App\Models\MemberClassLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberClassLogRepository implements MemberClassLogRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all class logs with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        try {
            $memberClassLogs = $this->getAllIndexData($request, MemberClassLog::where('member_id', auth()->user()->id)->get(), 'title');
            return MemberResponse::success('Member Class Logs retrieved successfully.', $memberClassLogs);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to store class log
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store($data)
    {
        try {
            $data['member_id'] = auth()->user()->id;

            if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imageName = Str::uuid() . '.' . $data['image']->getClientOriginalExtension();
                $path = $data['image']->storeAs('uploads/class_logs', $imageName, 'public');
                $data['image'] = $path;
            }

            $classLog = MemberClassLog::create($data);

            return MemberResponse::success('Class Log created successfully.', $classLog);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to show single class log
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $classLog = MemberClassLog::with('member')
            ->where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();
            return MemberResponse::success('Class Log retrieved successfully.', $classLog);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to update class log
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        try {
            $classLog = MemberClassLog::where('id', $id)
                ->where('member_id', auth()->id())
                ->firstOrFail();

            if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if it exists
                if ($classLog->image && Storage::disk('public')->exists($classLog->image)) {
                    Storage::disk('public')->delete($classLog->image);
                }

                // Store new image
                $imageName = Str::uuid() . '.' . $data['image']->getClientOriginalExtension();
                $path = $data['image']->storeAs('uploads/class_logs', $imageName, 'public');
                $data['image'] = $path;
            } else {
                // Prevent overwriting existing image with null if not sending a new one
                unset($data['image']);
            }

            $classLog->update($data);

            return MemberResponse::success('Class Log updated successfully.', $classLog);

        } catch (\Exception $e) {
            return MemberResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Method to delete class log
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        try {
            $classLog = MemberClassLog::where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();

            $classLog->delete();

            return MemberResponse::success('Class Log deleted successfully.', $classLog);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
