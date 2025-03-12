<?php
namespace App\Services;

use App\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;

class PermissionService
{
    private $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function createPermission(array $data): Permission
    {
        return $this->permissionRepository->create($data);
    }

    public function updatePermission(int $id, array $data): Permission
    {
        $permission = $this->permissionRepository->getById($id);
        $this->permissionRepository->update($permission, $data);
        return $permission;
    }

    public function deletePermission(Permission $permission): bool
    {
        return $this->permissionRepository->delete($permission);
    }
}
