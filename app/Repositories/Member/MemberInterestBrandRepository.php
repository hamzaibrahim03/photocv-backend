<?php

namespace App\Repositories\Member;

use App\Traits\UtilityTrait;
use App\Http\Responses\MemberResponse;
use App\Models\MemberBrand;

class MemberInterestBrandRepository implements MemberInterestBrandRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all data with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        try {
            $memberNotes = $this->getAllMemberInterestsBrands($request, auth()->user()->id);
            return MemberResponse::success('Data retrieved successfully.', $memberNotes);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to store data
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store($data)
    {
        try {
            $data['member_id'] = auth()->id();

            // Only keep the one that has value
            if (empty($data['interest'])) {
                unset($data['interest']);
            } else {
                unset($data['brands']);
            }

            // Handle image upload
            if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $data['image']->store('uploads/member_interests', 'public');
                $data['image'] = $imagePath;
            } else {
                unset($data['image']);
            }

            $record = MemberBrand::create($data);

            return MemberResponse::success('Record created successfully.', $record);

        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to show single data
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $data = MemberBrand::with('member')
            ->where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();
            return MemberResponse::success('Data retrieved successfully.', $data);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to update data
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        try {
            $record = MemberBrand::where('id', $id)
                ->where('member_id', auth()->id())
                ->firstOrFail();

            // Only update one of the two: interest or brands
            if (!empty($data['interest'])) {
                $data['brands'] = null;
            } elseif (!empty($data['brands'])) {
                $data['interest'] = null;
            }

            // Handle image upload (optional)
            if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $data['image']->store('uploads/member_interests', 'public');
                $data['image'] = $imagePath;
            } else {
                unset($data['image']); // Don't update image if not present
            }

            // Filter out nulls so only provided fields are updated
            $cleanData = collect($data)->filter(function ($value) {
                return !is_null($value);
            })->toArray();

            $record->update($cleanData);

            return MemberResponse::success('Record updated successfully.', $record);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return MemberResponse::error('Record not found or unauthorized.', 404);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to delete data
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        try {
            $record = MemberBrand::where('id', $id)
            ->where('member_id', auth()->user()->id)
            ->firstOrFail();

            $record->delete();

            return MemberResponse::success('Data deleted successfully.', $record);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
