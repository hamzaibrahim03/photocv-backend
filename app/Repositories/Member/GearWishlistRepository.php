<?php

namespace App\Repositories\Member;

use App\Http\Resources\GearWishlistResource;
use App\Http\Responses\MemberResponse;
use App\Models\GearWishlist;
use App\Traits\DataTables\GearDataTableTrait;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GearWishlistRepository implements GearWishlistRepositoryInterface
{
    use GearDataTableTrait;

    /**
     * Get wish list / gift list items of auth member (filter with list_type=wish|gift).
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function all($request)
    {
        try {
            $items = $this->getMemberWishlists($request, auth()->id());
            return MemberResponse::success('Wishlist items retrieved successfully.', $items);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to find single wishlist item of auth member
     * @param mixed $id
     * @return GearWishlist
     */
    public function find($id)
    {
        return GearWishlist::with('category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }

    /**
     * Method to store wishlist item
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data)
    {
        try {
            $data['user_id'] = auth()->id();

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = $data['image']->store('gear_wishlists', 'public');
            }

            $item = GearWishlist::create($data);

            return MemberResponse::success('Wishlist item created successfully.', new GearWishlistResource($item->load('category')), 201);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to update wishlist item
     * @param mixed $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, array $data)
    {
        try {
            $item = $this->find($id);

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                $data['image'] = $data['image']->store('gear_wishlists', 'public');
            }

            $item->update($data);

            return MemberResponse::success('Wishlist item updated successfully.', new GearWishlistResource($item->fresh('category')));
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to delete wishlist item
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $item = $this->find($id);

            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->delete();

            return MemberResponse::success('Wishlist item deleted successfully.', null);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }
}
