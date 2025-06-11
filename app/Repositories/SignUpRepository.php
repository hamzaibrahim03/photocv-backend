<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Club;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class SignUpRepository implements SignUpRepositoryInterface
{
    public function create(array $data): User
    {
        // Create user
        $user = User::create($data);

        // Assign role
        if (!empty($data['role'])) {
            $role = Role::findByName($data['role']);
            $user->assignRole($role);
        }

        // Assign role to the user
        if ( isset( $data['role'] ) ) {
            $role = Role::findByName( $data['role'] );
            $user->assignRole($role);
        }

        if (!empty($data['club'])) {
            $clubData = $data['club'];
            $clubData['user_id'] = $user->id; // Link user to club

            Club::create($clubData);
        }

        return $user;
    }
}
