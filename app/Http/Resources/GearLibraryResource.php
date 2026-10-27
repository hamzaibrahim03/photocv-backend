<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GearLibraryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Gears are grouped the way the kit card/modal shows them:
     * cameras, lenses, and everything else as accessories.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Every linked item gets its full gear record (same shape as GET /gears/{id}),
        // not just a handful of summary fields.
        $gears = $this->gears->map(fn ($gear) => (new GearResource($gear))->resolve($request));

        // Match on keyword so "Camera", "Camera Body", "Lens", "Lenses" all group correctly
        $isCamera = fn ($gear) => str_contains(strtolower($gear['category']['name'] ?? ''), 'camera');
        $isLens   = fn ($gear) => str_contains(strtolower($gear['category']['name'] ?? ''), 'lens');

        $cameras     = $gears->filter($isCamera)->values();
        $lenses      = $gears->filter($isLens)->values();
        $accessories = $gears->reject(fn ($gear) => $isCamera($gear) || $isLens($gear))->values();

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'cameras'     => $cameras,
            'lenses'      => $lenses,
            'accessories' => $accessories,
            'counts'      => [
                'cameras'     => $cameras->count(),
                'lenses'      => $lenses->count(),
                'accessories' => $accessories->count(),
            ],
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
