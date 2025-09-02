<?php

namespace App\Repositories\Member;

use App\Http\Responses\MemberResponse;
use App\Traits\DataTables\PlansDataTableTrait;
use App\Models\PlannedLocation;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlannedLocationResource;

class PlannedLocationRepository implements PlannedLocationRepositoryInterface
{
    use PlansDataTableTrait;

    /**
     * Get all planned locations of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function all($request)
    {
        try {
            $memberLocations = $this->getMemberLocations($request, auth()->user()->id);
            return MemberResponse::success('Member planned locations retrieved successfully.', $memberLocations);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to find single planned location
     * @param mixed $id
     * @return PlannedLocation
     */
    public function find($id)
    {
        return PlannedLocation::where('user_id', auth()->id())->findOrFail($id);
    }

    /**
     * Method to store planned location
     * @param array $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $data['user_id'] = auth()->id();

            if (isset($data['feature_image']) && $data['feature_image'] instanceof UploadedFile) {
                $path = $data['feature_image']->store('planned_locations', 'public');
                $data['feature_image'] = $path;
            }

            $location = PlannedLocation::create($data);

            DB::commit();

            return MemberResponse::success('Created successfully.', $location, 201);

        } catch (Exception $e) {
            DB::rollBack();

            return MemberResponse::error(
                $e->getMessage(),
                $e->getCode() > 0 ? $e->getCode() : 500
            );
        }
    }

    /**
     * Method to update planned location
     * @param array $data
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update($id, array $data)
    {
        try {
            $location = $this->find($id);

            // Handle feature image update
            if (isset($data['feature_image']) && $data['feature_image'] instanceof UploadedFile) {
                
                // Remove old image if exists
                if ($location->feature_image && Storage::exists($location->feature_image)) {
                    Storage::delete($location->feature_image);
                }

                // Store new image
                $data['feature_image'] = $data['feature_image']->store('planned_locations', 'public');
            }

            $location->update($data);

            return MemberResponse::success('Updated successfully.', new PlannedLocationResource($location), 201);

        } catch (\Exception $e) {
            return MemberResponse::error(
                $e->getMessage(),
                $e->getCode() > 0 ? $e->getCode() : 500
            );
        }
    }

    /**
     * Method to delete planned location
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $location = $this->find($id);

            if (!$location) {
                return MemberResponse::error('Location not found.', 404);
            }

            // Delete feature image if it exists
            if (!empty($location->feature_image) && \Storage::exists($location->feature_image)) {
                \Storage::delete($location->feature_image);
            }

            $location->delete();

            return MemberResponse::success('Location deleted successfully.', null, 200);
        } catch (\Exception $e) {
            return MemberResponse::error(
                $e->getMessage(),
                $e->getCode() > 0 ? $e->getCode() : 500
            );
        }
    }

}
