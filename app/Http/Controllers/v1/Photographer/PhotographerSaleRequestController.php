<?php

namespace App\Http\Controllers\v1\Photographer;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Services\Photographer\PhotographerSaleService;

class PhotographerSaleRequestController extends Controller
{
    private PhotographerSaleService $saleService;

    public function __construct(PhotographerSaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    /**
     * Incoming buy requests against the authenticated member's own listings.
     */
    public function index()
    {
        $requests = $this->saleService->myRequests(auth()->id());
        return MemberResponse::success('Requests retrieved successfully.', $requests);
    }

    public function accept(string $id)
    {
        $result = $this->saleService->respondToRequest(auth()->id(), (int) $id, 'accepted');
        return MemberResponse::success('Request accepted.', $result);
    }

    public function reject(string $id)
    {
        $result = $this->saleService->respondToRequest(auth()->id(), (int) $id, 'rejected');
        return MemberResponse::success('Request rejected.', $result);
    }
}
