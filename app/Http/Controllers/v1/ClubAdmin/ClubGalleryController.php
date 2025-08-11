<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubAdmin\ClubGalleryService;

class ClubGalleryController extends Controller
{
    private $clubGalleryService;

    public function __construct(ClubGalleryService $clubGalleryService)
    {
        $this->clubGalleryService = $clubGalleryService;
    }

    /**
     * Method to get club galleries
     * @param \Illuminate\Http\Request $request
     */
    public function index()
    {
        return $this->clubGalleryService->getClubGalleries(auth()->user()->id);
    }

    /**
     * Method to get club gallery information
     * @param mixed $id
     */
    public function show($id)
    {
        return $this->clubGalleryService->getClubGallery($id);
    }

    /**
     * Method to create club gallery
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        return $this->clubGalleryService->createClubGallery($request->all());
    }
}
