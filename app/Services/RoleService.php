<?php
namespace App\Services;

use App\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Facades\Event;

class RoleService
{
    private $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function createRole(array $data): Role
    {
        $role = $this->roleRepository->create($data);
        // You can dispatch events here if needed
        return $role;
    }

    public function updateRole(int $id, array $data): Role
    {
        $role = $this->roleRepository->getById($id);
        $this->roleRepository->update($role, $data);
        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        return $this->roleRepository->delete($role);
    }
}
