<?php

namespace App\Http\Controllers\v1\Super;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

// Roles whose removal/deactivation would break core auth flows
// (signup assigns club_admin, member signup assigns member, etc.).
const SUPER_PROTECTED_ROLES = ['super_admin', 'club_admin', 'member'];

class SuperRoleController extends Controller
{
    public function index(Request $request)
    {
        $counts = DB::table('model_has_roles')
            ->select('role_id', DB::raw('count(*) as total'))
            ->groupBy('role_id')
            ->pluck('total', 'role_id');

        $roles = Role::orderBy('id')->get()->map(fn ($role) => $this->formatRole($role, $counts[$role->id] ?? 0));
        return MemberResponse::success('Roles retrieved successfully.', $roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:125|unique:roles,name',
            'description' => 'nullable|string',
            'permission_label' => 'nullable|string|max:100',
        ]);

        $role = Role::create([
            'name' => $request->input('name'),
            'guard_name' => 'web',
            'description' => $request->input('description'),
            'permission_label' => $request->input('permission_label'),
            'is_active' => true,
        ]);

        return MemberResponse::success('Role created successfully.', $this->formatRole($role), 201);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:125|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permission_label' => 'nullable|string|max:100',
        ]);

        $role->update($request->only(['name', 'description', 'permission_label']));

        return MemberResponse::success('Role updated successfully.', $this->formatRole($role->fresh()));
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, SUPER_PROTECTED_ROLES, true)) {
            return MemberResponse::error('This role is required by the platform and cannot be deleted.', 422);
        }

        $role->delete();
        return MemberResponse::success('Role deleted successfully.', null);
    }

    public function toggleStatus(string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, SUPER_PROTECTED_ROLES, true)) {
            return MemberResponse::error('This role is required by the platform and cannot be deactivated.', 422);
        }

        $role->is_active = !$role->is_active;
        $role->save();

        return MemberResponse::success('Role status updated.', $this->formatRole($role));
    }

    private function formatRole(Role $role, int $assignedCount = 0): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'assignedClubs' => $assignedCount,
            'permissions' => $role->permission_label ?? 'None',
            'status' => (bool) $role->is_active,
        ];
    }
}
