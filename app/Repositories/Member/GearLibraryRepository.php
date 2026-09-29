<?php

namespace App\Repositories\Member;

use App\Http\Resources\GearLibraryResource;
use App\Http\Responses\MemberResponse;
use App\Models\GearLibrary;
use App\Traits\DataTables\GearDataTableTrait;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GearLibraryRepository implements GearLibraryRepositoryInterface
{
    use GearDataTableTrait;

    /**
     * Get all gear libraries (kits) of auth member.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function all($request)
    {
        try {
            $libraries = $this->getMemberGearLibraries($request, auth()->id());
            return MemberResponse::success('Gear libraries retrieved successfully.', $libraries);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to find single gear library of auth member
     * @param mixed $id
     * @return GearLibrary
     */
    public function find($id)
    {
        return GearLibrary::with('gears.category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }

    /**
     * Method to store gear library
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $library = GearLibrary::create(Arr::except($data, 'gear_ids') + ['user_id' => auth()->id()]);
            $library->gears()->sync($data['gear_ids'] ?? []);

            DB::commit();

            return MemberResponse::success('Gear library created successfully.', new GearLibraryResource($library->load('gears.category')), 201);
        } catch (Exception $e) {
            DB::rollBack();
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to update gear library
     * @param mixed $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, array $data)
    {
        try {
            DB::beginTransaction();

            $library = $this->find($id);
            $library->update(Arr::except($data, 'gear_ids'));

            if (array_key_exists('gear_ids', $data)) {
                $library->gears()->sync($data['gear_ids']);
            }

            DB::commit();

            return MemberResponse::success('Gear library updated successfully.', new GearLibraryResource($library->fresh('gears.category')));
        } catch (Exception $e) {
            DB::rollBack();
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to delete gear library (the gears themselves are kept)
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $this->find($id)->delete();
            return MemberResponse::success('Gear library deleted successfully.', null);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }
}
