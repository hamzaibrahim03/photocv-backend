<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlannedLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'feature_image' => $this->feature_image 
                            ? asset('storage/' . $this->feature_image) 
                            : null,
            'location'      => $this->location,
            'description'   => $this->description,
            'visited'       => $this->visited,
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
}
