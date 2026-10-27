<?php

namespace App\Http\Controllers\v1\Photographer;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Services\Photographer\PhotographerSaleService;
use Illuminate\Http\Request;

class PhotographerSaleController extends Controller
{
    private PhotographerSaleService $saleService;

    public function __construct(PhotographerSaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function index()
    {
        $sales = $this->saleService->mySales(auth()->id());
        return MemberResponse::success('Sales retrieved successfully.', $sales);
    }

    /**
     * Other members' active listings, so this member can browse and
     * request photos someone else has put up for sale.
     */
    public function browse()
    {
        $sales = $this->saleService->browseSales(auth()->id());
        return MemberResponse::success('Sales retrieved successfully.', $sales);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'photo_id' => 'nullable|integer',
            'title' => 'required|string|max:250',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'condition' => 'nullable|in:new,used',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:8',
            'status' => 'nullable|in:active,sold',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $sale = $this->saleService->createSale(auth()->id(), $data, $request->file('image'));

        return MemberResponse::success('Listing created successfully.', $sale, 201);
    }

    public function show(string $id)
    {
        $sale = $this->saleService->showSale(auth()->id(), (int) $id);
        return MemberResponse::success('Listing retrieved successfully.', $sale);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:250',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'condition' => 'nullable|in:new,used',
            'price' => 'sometimes|required|numeric|min:0',
            'status' => 'nullable|in:active,sold',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $sale = $this->saleService->updateSale(auth()->id(), (int) $id, $data, $request->file('image'));

        return MemberResponse::success('Listing updated successfully.', $sale);
    }

    public function destroy(string $id)
    {
        $this->saleService->deleteSale(auth()->id(), (int) $id);
        return MemberResponse::success('Listing removed successfully.', null);
    }

    /**
     * Any other member requesting to buy this listing.
     */
    public function request(string $id)
    {
        $result = $this->saleService->createRequest(auth()->id(), (int) $id);
        return MemberResponse::success('Request sent successfully.', $result, 201);
    }
}
