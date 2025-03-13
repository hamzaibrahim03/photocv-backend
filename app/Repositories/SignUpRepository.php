<?php
namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class SignUpRepository implements SignUpRepositoryInterface
{
    public function create(array $data): User
    {
        $user = User::create($data);

        // Assign role to the user
        if ( isset( $data['role'] ) ) {
            $role = Role::findByName( $data['role'] );
            $user->assignRole($role);
        }

        return $user;
    }
}

?>
