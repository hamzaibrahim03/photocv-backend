<?php

namespace App\Traits\DataTables;

use App\Http\Resources\CheatSheetResource;
use App\Http\Resources\GearLibraryResource;
use App\Http\Resources\GearResource;
use App\Http\Resources\GearWishlistResource;
use App\Models\CheatSheet;
use App\Models\Gear;
use App\Models\GearLibrary;
use App\Models\GearWishlist;
use Yajra\DataTables\Facades\DataTables;

trait GearDataTableTrait
{
    use CommonDataTableTrait;

    /**
     * Member gears with the filters from the gear listing screen
     * (category, system, type, model name, score/rating, for sale).
     */
    public function getMemberGears($request, $userId)
    {
        $query = Gear::query()
            ->with(['category', 'images'])
            ->where('user_id', $userId);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        foreach (['system', 'type', 'model_name'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', (int) $request->rating);
        }

        if ($request->has('is_for_sale')) {
            $query->where('is_for_sale', $request->boolean('is_for_sale'));
        }

        $query = $this->applySearchAndOrder($query, $request, 'created_at', ['title', 'brand', 'model', 'model_name', 'description']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->setTransformer(function ($gear) {
                return (new GearResource($gear))->resolve();
            })
            ->make(true);
    }

    public function getMemberGearLibraries($request, $userId)
    {
        $query = GearLibrary::query()
            ->with('gears.category')
            ->where('user_id', $userId);

        $query = $this->applySearchAndOrder($query, $request, 'created_at', ['title', 'description']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->setTransformer(function ($library) {
                return (new GearLibraryResource($library))->resolve();
            })
            ->make(true);
    }

    public function getMemberWishlists($request, $userId)
    {
        $query = GearWishlist::query()
            ->with('category')
            ->where('user_id', $userId);

        if ($request->filled('list_type')) {
            $query->where('list_type', $request->list_type);
        }

        $query = $this->applySearchAndOrder($query, $request, 'created_at', ['title']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->setTransformer(function ($item) {
                return (new GearWishlistResource($item))->resolve();
            })
            ->make(true);
    }

    public function getMemberCheatSheets($request, $userId)
    {
        $query = CheatSheet::query()
            ->with('category')
            ->where('user_id', $userId);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        $query = $this->applySearchAndOrder($query, $request, 'created_at', ['title', 'description']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->setTransformer(function ($sheet) {
                return (new CheatSheetResource($sheet))->resolve();
            })
            ->make(true);
    }
}
