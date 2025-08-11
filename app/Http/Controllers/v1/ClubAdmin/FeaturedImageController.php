<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeatureImage\FeatureImageRequest;
use App\Services\ClubAdmin\FeatureImageService;

class FeaturedImageController extends Controller
{
    private $featureImageService;

    public function __construct(FeatureImageService $featureImageService)
    {
        $this->featureImageService = $featureImageService;
    }

    public function assign( FeatureImageRequest $request )
    {
        return $this->featureImageService->assignFeatureImage( $request->validated() );
    }
}
