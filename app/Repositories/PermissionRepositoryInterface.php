<?php
namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?Permission;
    public function create(array $data): Permission;
    public function update(Permission $permission, array $data): bool;
    public function delete(Permission $permission): bool;
}

?>