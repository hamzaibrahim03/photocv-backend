<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GearWishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'list_type'    => $this->list_type,
            'title'        => $this->title,
            'category'     => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'category_id'  => $this->category_id,
            'price'        => $this->price,
            'image'        => $this->image ? asset('storage/' . $this->image) : null,
            'link'         => $this->link,
            'is_purchased' => $this->is_purchased,
            'created_at'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
