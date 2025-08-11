<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Member\MemberNoteService;
use App\Http\Requests\Member\MemberNoteRequest;

class MemberNoteController extends Controller
{
    private $memberNoteService;

    public function __construct(MemberNoteService $memberNoteService)
    {
        $this->memberNoteService = $memberNoteService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->memberNoteService->allMemberNotes( $request );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberNoteRequest $request)
    {
        return $this->memberNoteService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->memberNoteService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MemberNoteRequest $request, string $id)
    {
        return $this->memberNoteService->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->memberNoteService->delete($id);
    }
}
