<?php

namespace App\Http\Resources;

use App\Models\Gear;
use Illuminate\Http\Resources\Json\JsonResource;

class GearResource extends JsonResource
{
    /**
     * Label for a 0–100 suitability value, matching the five slider stops
     * on the gear form (Not Recommended … Perfect Fit).
     */
    public static function suitabilityLabel(?int $value): string
    {
        return match (true) {
            $value >= 80 => 'Perfect Fit',
            $value >= 60 => 'Highly Suitable',
            $value >= 40 => 'Moderately Suitable',
            $value >= 20 => 'Occasionally Usable',
            default      => 'Not Recommended',
        };
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $suitability = [];
        foreach (Gear::SUITABILITY_FIELDS as $field) {
            $key = str_replace('suitability_', '', $field);
            $suitability[$key] = [
                'value' => (int) $this->{$field},
                'label' => self::suitabilityLabel((int) $this->{$field}),
            ];
        }

        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'image'        => $this->image ? asset('storage/' . $this->image) : null,

            // Basic gear information
            'category'     => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'category_id'  => $this->category_id,
            'system'       => $this->system,
            'type'         => $this->type,
            'brand'        => $this->brand,
            'model'        => $this->model,
            'model_name'   => $this->model_name,
            'storage_name' => $this->storage_name,

            // Purchase & ownership
            'purchase_price'  => $this->purchase_price,
            'purchase_date'   => $this->purchase_date?->format('Y-m-d'),
            'vendor'          => $this->vendor,
            'serial_number'   => $this->serial_number,
            'insured'         => $this->insured,
            'firmware_link'   => $this->firmware_link,
            'ownership_type'  => $this->ownership_type,
            'warranty_expiry' => $this->warranty_expiry?->format('Y-m-d'),

            // Technical specifications
            'mount'              => $this->mount,
            'dimensions'         => $this->dimensions,
            'weight'             => $this->weight,
            'min_focal_length'   => $this->min_focal_length,
            'max_focal_length'   => $this->max_focal_length,
            'min_aperture'       => $this->min_aperture,
            'max_aperture'       => $this->max_aperture,
            'min_focus_distance' => $this->min_focus_distance,
            'max_focus_distance' => $this->max_focus_distance,
            'max_magnification'  => $this->max_magnification,
            'sensor_type'        => $this->sensor_type,
            'resolution'         => $this->resolution,
            'iso_range'          => $this->iso_range,

            'suitability' => $suitability,

            // Maintenance & status
            'shutter_count'    => $this->shutter_count,
            'priority_tag'     => $this->priority_tag,
            'notes'            => $this->notes,
            'condition'        => $this->condition,
            'is_available'     => $this->is_available,
            'last_serviced_at' => $this->last_serviced_at?->format('Y-m-d'),
            'rating'           => $this->rating,

            // Sales
            'is_for_sale' => $this->is_for_sale,
            'sale_price'  => $this->sale_price,
            'sold_at'     => $this->sold_at?->toDateTimeString(),

            'gear_images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($image) => [
                    'id'  => $image->id,
                    'url' => $image->image_url,
                ]);
            }, []),

            'photos_using_gear' => $this->whenLoaded('photos', function () {
                return $this->photos->map(fn ($photo) => [
                    'id'        => $photo->id,
                    'title'     => $photo->title,
                    'thumb_url' => $photo->thumb_url ?? $photo->image_url,
                ]);
            }, []),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
