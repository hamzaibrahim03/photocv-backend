<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class GalleryRequest extends FormRequest
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
        $route = $this->route()->getActionMethod();

        // Define validation rules based on the route
        if ($route === 'createGallery') {
            return [
                'gallery_name' => 'required|string|max:255',
                'is_active'    => 'required|boolean',
            ];
        }

        if ($route === 'uploadGalleryImages') {
            return [
                'gallery_id'    => 'required|integer|exists:member_galleries,id',
                'gallery_name'  => 'required|string|max:255',
                'is_active'     => 'required|boolean',
                'images'        => 'required|array|min:1',
                'images.*'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'title'         => 'required|array|min:1',
                'title.*'       => 'nullable|string|max:250',
                'description'   => 'required|array|min:1',
                'description.*' => 'nullable|string',
            ];
        }

        return [];
    }
}
