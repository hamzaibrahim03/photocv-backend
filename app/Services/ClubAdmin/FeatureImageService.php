<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\FeatureImageRepositoryInterface;

class FeatureImageService
{
    private $featureImageRepository;

    public function __construct(FeatureImageRepositoryInterface $featureImageRepository)
    {
        $this->featureImageRepository = $featureImageRepository;
    }

    public function assignFeatureImage( $request )
    {
        return $this->featureImageRepository->assignFeatureImage( $request );
    }

}
