<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemberInterestBrandService;
use App\Http\Requests\Member\MemberInterestBrandRequest;

class MemberInterestBrandController extends Controller
{
    private $memberInterestBrandService;

    public function __construct(MemberInterestBrandService $memberInterestBrandService)
    {
        $this->memberInterestBrandService = $memberInterestBrandService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->memberInterestBrandService->index( $request );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberInterestBrandRequest $request)
    {
        return $this->memberInterestBrandService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->memberInterestBrandService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MemberInterestBrandRequest $request, string $id)
    {
        return $this->memberInterestBrandService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->memberInterestBrandService->delete($id);
    }
}
