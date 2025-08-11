<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubAdmin\PageService;
use App\Http\Requests\Pages\StorePageRequest;
use App\Http\Requests\Pages\UpdatePageRequest;

class PagesController extends Controller
{
    private $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public function index( Request $request )
    {
        return $this->pageService->allPages( $request );
    }

    public function show( $id ) {
        return $this->pageService->showPage( $id );
    }

    public function store(StorePageRequest $request)
    {
        return $this->pageService->createPage($request->except('thumb_image'), $request->file('thumb_image'));
    }

    public function update(UpdatePageRequest $request, $id)
    {
        return $this->pageService->updatePage( $id, $request->except('thumb_image'), $request->file('thumb_image') );
    }

    public function destroy( $id )
    {
        return $this->pageService->deletePage( $id );
    }

    public function getPagesExtras( Request $request ) {
        return $this->pageService->getPagesExtras( $request );
    }
}
