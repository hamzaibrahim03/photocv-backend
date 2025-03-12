<?php
namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->userRepository->getById($id);
        $this->userRepository->update($user, $data);
        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }
}

?>