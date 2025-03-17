<?php

namespace App\Repositories;

interface CatalogRepositoryInterface
{
    public function all($catalog_type);
    public function create(array $data, $file);
    public function update($id, array $data, $file);
    public function delete($id);
}
