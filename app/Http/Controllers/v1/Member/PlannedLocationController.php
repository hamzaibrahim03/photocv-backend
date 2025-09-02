<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Member\PlannedLocationService;
use App\Http\Requests\PlannedLocation\StorePlannedLocationRequest;
use App\Http\Requests\PlannedLocation\UpdatePlannedLocationRequest;

class PlannedLocationController extends Controller
{
    protected $plannedLocationService;

    public function __construct(PlannedLocationService $plannedLocationService)
    {
        $this->plannedLocationService = $plannedLocationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->plannedLocationService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlannedLocationRequest $request)
    {
        return $this->plannedLocationService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->plannedLocationService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlannedLocationRequest $request, string $id)
    {
        return $this->plannedLocationService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->plannedLocationService->delete($id);
    }
}
