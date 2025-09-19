<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlannedDayOut\StorePlannedDayOutRequest;
use App\Http\Requests\PlannedDayOut\UpdatePlannedDayOutRequest;
use App\Models\PlannedDayOut;
use App\Services\Member\PlannedDayOutService;

class PlannedDayOutController extends Controller
{
    protected $service;

    public function __construct(PlannedDayOutService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->listUserPlans();
    }

    public function store(StorePlannedDayOutRequest $request)
    {
        return $this->service->createPlan($request->validated());
    }

    public function show(PlannedDayOut $plannedDayOut)
    {
        return $this->service->getPlan($plannedDayOut);
    }

    public function update(UpdatePlannedDayOutRequest $request, PlannedDayOut $plannedDayOut)
    {
        return $this->service->updatePlan($plannedDayOut, $request->validated());
    }

    public function destroy(PlannedDayOut $plannedDayOut)
    {
        return $this->service->deletePlan($plannedDayOut);
    }
}
