<?php

namespace App\Traits\DataTables;
use App\Http\Resources\PlannedLocationResource;
use App\Models\PlannedLocation;
use Yajra\DataTables\Facades\DataTables;

trait PlansDataTableTrait
{
    use CommonDataTableTrait;

    public function getMemberLocations($request, $userId)
    {
        $query = PlannedLocation::query()->where('user_id', $userId);

        // Apply shared search + ordering
        $query = $this->applySearchAndOrder($query, $request, 'created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->setTransformer(function ($location) {
                return (new PlannedLocationResource($location))->resolve();
            })
            ->make(true);
    }

}
