<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AuthRepository implements AuthRepositoryInterface
{


    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function revokeTokens(User $user): void
    {
        // Revoke all tokens for the user
        $user->tokens()->delete();

        // Set email_verified_at to null
    }


}

?>
