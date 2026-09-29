<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Member\GearService;
use App\Http\Requests\Gear\GearRequest;

class GearController extends Controller
{
    protected $gearService;

    public function __construct(GearService $gearService)
    {
        $this->gearService = $gearService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->gearService->index($request);
    }

    /**
     * Dropdown/filter data for gear screens.
     */
    public function extras()
    {
        return $this->gearService->extras();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GearRequest $request)
    {
        return $this->gearService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->gearService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GearRequest $request, string $id)
    {
        return $this->gearService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->gearService->delete($id);
    }
}
