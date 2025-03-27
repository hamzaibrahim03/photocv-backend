<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubNewsService;
use App\Http\Requests\ClubNews\StoreClubNewsRequest;
use App\Http\Requests\ClubNews\UpdateClubNewsRequest;

class ClubNewsController extends Controller
{
    private $clubNewsService;

    public function __construct(ClubNewsService $clubNewsService)
    {
        $this->clubNewsService = $clubNewsService;
    }

    public function index( Request $request )
    {
        return $this->clubNewsService->allClubNews( $request );
    }

    public function show( $id ) {
        return $this->clubNewsService->showClubNews( $id );
    }

    public function store(StoreClubNewsRequest $request)
    {
        return $this->clubNewsService->createClubNews($request->except('thumb_image'), $request->file('thumb_image'));
    }

    public function update(UpdateClubNewsRequest $request, $id)
    {
        return $this->clubNewsService->updateClubNews( $id, $request->except('thumb_image'), $request->file('thumb_image') );
    }

    public function destroy( $id )
    {
        return $this->clubNewsService->deleteClubNews( $id );
    }
}
