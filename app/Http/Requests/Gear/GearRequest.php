<?php

namespace App\Http\Requests\Gear;

use App\Models\Gear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title'        => ($this->isMethod('POST') ? 'required' : 'sometimes|required') . '|string|max:255',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',

            // Basic gear information
            'category_id'  => [
                'nullable',
                'integer',
                Rule::exists('catalog', 'id')->where('catalog_type', 'gear_category'),
            ],
            'system'       => 'nullable|string|max:255',
            'type'         => 'nullable|string|max:255',
            'brand'        => 'nullable|string|max:255',
            'model'        => 'nullable|string|max:255',
            'model_name'   => 'nullable|string|max:255',
            'storage_name' => 'nullable|string|max:255',

            // Purchase & ownership
            'purchase_price'  => 'nullable|numeric|min:0',
            'purchase_date'   => 'nullable|date',
            'vendor'          => 'nullable|string|max:255',
            'serial_number'   => 'nullable|string|max:255',
            'insured'         => 'nullable|boolean',
            'firmware_link'   => 'nullable|url|max:500',
            'ownership_type'  => 'nullable|string|max:50',
            'warranty_expiry' => 'nullable|date',

            // Technical specifications
            'mount'              => 'nullable|string|max:255',
            'dimensions'         => 'nullable|numeric|min:0',
            'weight'             => 'nullable|string|max:50',
            'min_focal_length'   => 'nullable|numeric|min:0',
            'max_focal_length'   => 'nullable|numeric|min:0|gte:min_focal_length',
            'min_aperture'       => 'nullable|numeric|min:0',
            'max_aperture'       => 'nullable|numeric|min:0|gte:min_aperture',
            'min_focus_distance' => 'nullable|numeric|min:0',
            'max_focus_distance' => 'nullable|numeric|min:0|gte:min_focus_distance',
            'max_magnification'  => 'nullable|numeric|min:0',
            'sensor_type'        => 'nullable|string|max:255',
            'resolution'         => 'nullable|string|max:255',
            'iso_range'          => 'nullable|string|max:255',

            // Maintenance & status
            'shutter_count'    => 'nullable|integer|min:0',
            'priority_tag'     => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'condition'        => 'nullable|string|max:50',
            'is_available'     => 'nullable|boolean',
            'last_serviced_at' => 'nullable|date',
            'rating'           => 'nullable|integer|min:1|max:10',

            // Sales
            'is_for_sale' => 'nullable|boolean',
            'sale_price'  => 'nullable|numeric|min:0',
            'sold_at'     => 'nullable|date',

            // Photos of gear (uploads) and photos using gear (from gallery)
            'gear_images'              => 'nullable|array|max:10',
            'gear_images.*'            => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_gear_image_ids'    => 'nullable|array',
            'remove_gear_image_ids.*'  => 'integer',
            'photo_ids'                => 'nullable|array',
            'photo_ids.*'              => [
                'integer',
                Rule::exists('photos', 'id')->where('uploaded_by', auth()->id())->whereNull('deleted_at'),
            ],
        ];

        foreach (Gear::SUITABILITY_FIELDS as $field) {
            $rules[$field] = 'nullable|integer|min:0|max:100';
        }

        return $rules;
    }
}
