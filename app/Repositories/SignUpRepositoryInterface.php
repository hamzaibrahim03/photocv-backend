<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface SignUpRepositoryInterface
{
    public function create(array $data): User;
    public function exists(string $email, string $username);

    public function alreadyRegisteredDomain(array $data);
}
