<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemberClassLogService;
use App\Http\Requests\Member\MemberClassLogRequest;

class MemberClassLogController extends Controller
{
    private $memberClassLogService;

    public function __construct(MemberClassLogService $memberClassLogService)
    {
        $this->memberClassLogService = $memberClassLogService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->memberClassLogService->index( $request );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberClassLogRequest $request)
    {
        return $this->memberClassLogService->store( $request->validated() );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->memberClassLogService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MemberClassLogRequest $request, string $id)
    {
        return $this->memberClassLogService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->memberClassLogService->delete($id);
    }
}
