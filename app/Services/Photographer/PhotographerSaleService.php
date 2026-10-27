<?php

namespace App\Services\Photographer;

use App\Models\Photo;
use App\Models\PhotoSale;
use App\Models\PhotoSaleRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PhotographerSaleService
{
    /**
     * The authenticated member's own sale listings, for the "My Sales" panel.
     */
    public function mySales(int $memberId)
    {
        return PhotoSale::where('member_id', $memberId)
            ->with('photo')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PhotoSale $sale) => $this->formatSale($sale));
    }

    /**
     * Other members' active listings, so a photographer can browse and
     * request photos someone else has put up for sale.
     */
    public function browseSales(int $memberId)
    {
        return PhotoSale::where('member_id', '!=', $memberId)
            ->where('status', 'active')
            ->with(['photo', 'member'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PhotoSale $sale) => $this->formatSale($sale, true));
    }

    /**
     * Create a new listing. Either linked to one of the member's own
     * photos (photo_id) or a directly uploaded image.
     */
    public function createSale(int $memberId, array $data, ?UploadedFile $image = null): array
    {
        $photoId = null;

        if (!empty($data['photo_id'])) {
            $photo = Photo::where('id', $data['photo_id'])
                ->where('uploaded_by', $memberId)
                ->first();

            if (!$photo) {
                throw ValidationException::withMessages([
                    'photo_id' => ['That photo was not found in your own galleries.'],
                ]);
            }

            $photoId = $photo->id;
        }

        $sale = PhotoSale::create([
            'member_id' => $memberId,
            'photo_id' => $photoId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'condition' => $data['condition'] ?? null,
            'price' => $data['price'],
            'currency' => $data['currency'] ?? 'GBP',
            'status' => $data['status'] ?? 'active',
            'image' => $image ? $image->store('photo_sales', 'public') : null,
        ]);

        return $this->formatSale($sale->fresh('photo'));
    }

    /**
     * The authenticated member's own single listing, for the detail/edit view.
     */
    public function showSale(int $memberId, int $saleId): array
    {
        $sale = PhotoSale::where('id', $saleId)->where('member_id', $memberId)->with('photo')->firstOrFail();
        return $this->formatSale($sale);
    }

    public function updateSale(int $memberId, int $saleId, array $data, ?UploadedFile $image = null): array
    {
        $sale = PhotoSale::where('id', $saleId)->where('member_id', $memberId)->firstOrFail();

        $updates = array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'condition' => $data['condition'] ?? null,
            'price' => $data['price'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn ($v) => $v !== null);

        if ($image) {
            if ($sale->image) {
                Storage::disk('public')->delete($sale->image);
            }
            $updates['image'] = $image->store('photo_sales', 'public');
        }

        $sale->update($updates);

        return $this->formatSale($sale->fresh('photo'));
    }

    public function deleteSale(int $memberId, int $saleId): void
    {
        $sale = PhotoSale::where('id', $saleId)->where('member_id', $memberId)->firstOrFail();
        if ($sale->image) {
            Storage::disk('public')->delete($sale->image);
        }
        $sale->delete();
    }

    /**
     * Incoming buy requests against the authenticated member's own listings,
     * for the "Photo Requests" panel.
     */
    public function myRequests(int $memberId)
    {
        return PhotoSaleRequest::whereHas('photoSale', fn ($q) => $q->where('member_id', $memberId))
            ->with(['requester', 'photoSale.photo'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PhotoSaleRequest $request) => $this->formatRequest($request));
    }

    /**
     * Any member (other than the listing's own owner) requesting to buy a
     * listed photo.
     */
    public function createRequest(int $requesterId, int $saleId): array
    {
        $sale = PhotoSale::findOrFail($saleId);

        if ($sale->member_id === $requesterId) {
            throw ValidationException::withMessages([
                'photo_sale_id' => ['You cannot request your own listing.'],
            ]);
        }

        $existing = PhotoSaleRequest::where('photo_sale_id', $saleId)
            ->where('requester_id', $requesterId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $this->formatRequest($existing->fresh(['requester', 'photoSale.photo']));
        }

        $request = PhotoSaleRequest::create([
            'photo_sale_id' => $saleId,
            'requester_id' => $requesterId,
            'status' => 'pending',
        ]);

        return $this->formatRequest($request->fresh(['requester', 'photoSale.photo']));
    }

    public function respondToRequest(int $memberId, int $requestId, string $status): array
    {
        $request = PhotoSaleRequest::whereHas('photoSale', fn ($q) => $q->where('member_id', $memberId))
            ->with(['requester', 'photoSale.photo'])
            ->findOrFail($requestId);

        $request->update(['status' => $status]);

        return $this->formatRequest($request->fresh(['requester', 'photoSale.photo']));
    }

    private function formatSale(PhotoSale $sale, bool $withSeller = false): array
    {
        $data = [
            'id' => $sale->id,
            'title' => $sale->title,
            'description' => $sale->description,
            'category' => $sale->category,
            'condition' => $sale->condition,
            'price' => (float) $sale->price,
            'currency' => $sale->currency,
            'status' => $sale->status,
            'image' => $sale->image ? asset('storage/' . $sale->image) : $sale->photo?->image_url,
            'sold_count' => $sale->requests()->where('status', 'accepted')->count(),
        ];

        if ($withSeller) {
            $seller = $sale->member;
            $data['seller'] = [
                'id' => $seller?->id,
                'name' => $seller ? (trim(($seller->first_name ?? '') . ' ' . ($seller->last_name ?? '')) ?: $seller->username) : 'Unknown',
                'avatar' => $seller?->profile_image_url,
            ];
        }

        return $data;
    }

    private function formatRequest(PhotoSaleRequest $request): array
    {
        $requester = $request->requester;
        return [
            'id' => $request->id,
            'status' => $request->status,
            'createdAt' => $request->created_at?->diffForHumans(),
            'requester' => [
                'id' => $requester?->id,
                'name' => $requester ? (trim(($requester->first_name ?? '') . ' ' . ($requester->last_name ?? '')) ?: $requester->username) : 'Unknown',
                'avatar' => $requester?->profile_image_url,
            ],
            'sale' => [
                'id' => $request->photoSale?->id,
                'title' => $request->photoSale?->title,
                'price' => $request->photoSale ? (float) $request->photoSale->price : null,
                'currency' => $request->photoSale?->currency,
                'image' => $request->photoSale?->photo?->image_url,
            ],
        ];
    }
}
