<?php

namespace App\Repositories\Member;

use App\Http\Resources\GearResource;
use App\Http\Responses\MemberResponse;
use App\Models\Gear;
use App\Traits\DataTables\GearDataTableTrait;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GearRepository implements GearRepositoryInterface
{
    use GearDataTableTrait;

    /**
     * Keys in the request that are not columns on the gears table.
     */
    private const RELATION_KEYS = ['gear_images', 'remove_gear_image_ids', 'photo_ids'];

    /**
     * Get all gears of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function all($request)
    {
        try {
            $gears = $this->getMemberGears($request, auth()->id());
            return MemberResponse::success('Member gears retrieved successfully.', $gears);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to find single gear of auth member
     * @param mixed $id
     * @return Gear
     */
    public function find($id)
    {
        return Gear::with(['category', 'images', 'photos'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }

    /**
     * Method to store gear
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $attributes = Arr::except($data, self::RELATION_KEYS);
            $attributes['user_id'] = auth()->id();

            if (isset($attributes['image']) && $attributes['image'] instanceof UploadedFile) {
                $attributes['image'] = $attributes['image']->store('gears', 'public');
            }

            $gear = Gear::create($attributes);

            $this->syncRelations($gear, $data);

            DB::commit();

            return MemberResponse::success('Gear created successfully.', new GearResource($gear->load(['category', 'images', 'photos'])), 201);
        } catch (Exception $e) {
            DB::rollBack();
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to update gear
     * @param mixed $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, array $data)
    {
        try {
            DB::beginTransaction();

            $gear = $this->find($id);
            $attributes = Arr::except($data, self::RELATION_KEYS);

            if (isset($attributes['image']) && $attributes['image'] instanceof UploadedFile) {
                if ($gear->image) {
                    Storage::disk('public')->delete($gear->image);
                }
                $attributes['image'] = $attributes['image']->store('gears', 'public');
            }

            $gear->update($attributes);

            $this->syncRelations($gear, $data);

            DB::commit();

            return MemberResponse::success('Gear updated successfully.', new GearResource($gear->fresh(['category', 'images', 'photos'])));
        } catch (Exception $e) {
            DB::rollBack();
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to delete gear with its uploaded images
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $gear = $this->find($id);

            $paths = $gear->images->pluck('image_path')->push($gear->image)->filter()->all();
            Storage::disk('public')->delete($paths);

            $gear->delete();

            return MemberResponse::success('Gear deleted successfully.', null);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Handle gear photo uploads/removals and linked gallery photos.
     * @param Gear $gear
     * @param array $data
     */
    private function syncRelations(Gear $gear, array $data): void
    {
        if (!empty($data['remove_gear_image_ids'])) {
            $images = $gear->images()->whereIn('id', $data['remove_gear_image_ids'])->get();
            Storage::disk('public')->delete($images->pluck('image_path')->all());
            $gear->images()->whereIn('id', $images->pluck('id'))->delete();
        }

        foreach ($data['gear_images'] ?? [] as $file) {
            $gear->images()->create([
                'image_path' => $file->store('gears/images', 'public'),
            ]);
        }

        if (array_key_exists('photo_ids', $data)) {
            $gear->photos()->sync($data['photo_ids'] ?? []);
        }
    }
}
