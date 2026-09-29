<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Member\CheatSheetService;
use App\Http\Requests\CheatSheet\CheatSheetRequest;

class CheatSheetController extends Controller
{
    protected $cheatSheetService;

    public function __construct(CheatSheetService $cheatSheetService)
    {
        $this->cheatSheetService = $cheatSheetService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->cheatSheetService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CheatSheetRequest $request)
    {
        return $this->cheatSheetService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->cheatSheetService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CheatSheetRequest $request, string $id)
    {
        return $this->cheatSheetService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->cheatSheetService->delete($id);
    }
}
