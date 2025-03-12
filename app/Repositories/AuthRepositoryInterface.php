<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function revokeTokens(User $user): void;
}
