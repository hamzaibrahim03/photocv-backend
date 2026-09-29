<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheatSheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'category'        => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'category_id'     => $this->category_id,
            'reference_image' => $this->reference_image
                                ? asset('storage/' . $this->reference_image)
                                : null,

            // Manual settings
            'shutter_min'  => $this->shutter_min,
            'shutter_max'  => $this->shutter_max,
            'iso_min'      => $this->iso_min,
            'iso_max'      => $this->iso_max,
            'aperture_min' => $this->aperture_min,
            'aperture_max' => $this->aperture_max,

            // Essential settings
            'lens'            => $this->lens,
            'camera'          => $this->camera,
            'white_balance'   => $this->white_balance,
            'noise_reduction' => $this->noise_reduction,

            'notes'       => $this->notes,
            'accessories' => $this->accessories ?? [],
            'tags'        => $this->tags ?? [],
            'visibility'  => $this->visibility,
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
