<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Catalog;

class CatalogRepository implements CatalogRepositoryInterface
{
    public function all( $catalog_type )
    {
        if ( isset( $catalog_type['catalog_type'] ) && ! empty( $catalog_type['catalog_type'] ) ) {
            return Catalog::where( 'catalog_type', $catalog_type['catalog_type'] )->get()->map(function ($catalog) {
                $catalog->icon_url = $catalog->icon ? asset(\Storage::url($catalog->icon)) : null;
                return $catalog;
            });
        } else {
            return Catalog::all()->map(function ($catalog) {
                $catalog->icon_url = $catalog->icon ? asset(\Storage::url($catalog->icon)) : null;
                return $catalog;
            });
        }
    }

    public function create(array $data, $file = null)
    {
        // Handle file upload
        if ( $file && $file->isValid() ) {
            $iconPath = $file->store('catalog_icons', 'public');
            $data['icon'] = $iconPath;
        }

        return Catalog::create($data);
    }

    public function update($id, array $data, $file = null)
    {
        $catalog = Catalog::findOrFail($id);

        if ( $file && $file->isValid() ) {
            // Delete the old icon if exists
            if ($catalog->icon && \Storage::disk('public')->exists($catalog->icon)) {
                \Storage::disk('public')->delete($catalog->icon);
            }

            // Store new icon
            $data['icon'] = $file->store('catalog_icons', 'public');
        }

        $catalog->update($data);
        return $catalog;
    }

    public function delete($id)
    {
        $catalog = Catalog::findOrFail($id);
        if ( $catalog ) {
            $catalog->delete();
            return 'Catalog Deleted Succesfully';
        }
        return $catalog;
    }

}
