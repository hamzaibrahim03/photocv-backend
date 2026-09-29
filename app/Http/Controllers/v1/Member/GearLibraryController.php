<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Member\GearLibraryService;
use App\Http\Requests\Gear\GearLibraryRequest;

class GearLibraryController extends Controller
{
    protected $gearLibraryService;

    public function __construct(GearLibraryService $gearLibraryService)
    {
        $this->gearLibraryService = $gearLibraryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->gearLibraryService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GearLibraryRequest $request)
    {
        return $this->gearLibraryService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->gearLibraryService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GearLibraryRequest $request, string $id)
    {
        return $this->gearLibraryService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->gearLibraryService->delete($id);
    }
}
