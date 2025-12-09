<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\CatalogRepositoryInterface;
use App\Models\Catalog;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    private $catalogRepository;

    public function __construct(CatalogRepositoryInterface $catalogRepository)
    {
        $this->catalogRepository = $catalogRepository;
    }

    public function allCatalogs( $catalog_type ): Collection
    {
        return $this->catalogRepository->all( $catalog_type );
    }

    public function createCatalog(array $data, $file): Catalog
    {
        return $this->catalogRepository->create($data, $file);
    }

    public function updateCatalog(int $id, array $data, $file)
    {
        return $this->catalogRepository->update($id, $data, $file);
    }

    public function deleteCatalog( $id )
    {
        return $this->catalogRepository->delete($id);
    }

    public function getAllCatalogGrouped()
    {
        return $this->catalogRepository->getAllGrouped();
    }

}
