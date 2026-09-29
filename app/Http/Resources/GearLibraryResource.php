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
        $gears = $this->gears->map(fn ($gear) => [
            'id'         => $gear->id,
            'title'      => $gear->title,
            'brand'      => $gear->brand,
            'model'      => $gear->model,
            'model_name' => $gear->model_name,
            'mount'      => $gear->mount,
            'resolution' => $gear->resolution,
            'image'      => $gear->image ? asset('storage/' . $gear->image) : null,
            'category'   => $gear->category?->name,
        ]);

        // Match on keyword so "Camera", "Camera Body", "Lens", "Lenses" all group correctly
        $isCamera = fn ($gear) => str_contains(strtolower($gear['category'] ?? ''), 'camera');
        $isLens   = fn ($gear) => str_contains(strtolower($gear['category'] ?? ''), 'lens');

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
