<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Member\GearWishlistService;
use App\Http\Requests\Gear\GearWishlistRequest;

class GearWishlistController extends Controller
{
    protected $gearWishlistService;

    public function __construct(GearWishlistService $gearWishlistService)
    {
        $this->gearWishlistService = $gearWishlistService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->gearWishlistService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GearWishlistRequest $request)
    {
        return $this->gearWishlistService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->gearWishlistService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GearWishlistRequest $request, string $id)
    {
        return $this->gearWishlistService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->gearWishlistService->delete($id);
    }
}
