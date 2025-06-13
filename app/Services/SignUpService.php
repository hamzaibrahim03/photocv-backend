<?php

namespace App\Services;

use App\Repositories\SignUpRepositoryInterface;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Collection;
use App\Events\ParentRegistered ;
use Illuminate\Support\Str; // For generating random string
use Illuminate\Support\Facades\Hash; // For hashing the password
use App\Mail\UserRegisteredMail;
use Illuminate\Support\Facades\Mail;
use App\Events\UserRegistered;


class SignUpService
{
    private $signUpRepository;

    public function __construct(SignUpRepositoryInterface $signUpRepository)
    {
        $this->signUpRepository = $signUpRepository;
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     */
    public function registerUser(array $data): User
    {
        $password = $data['password'];
        $data['password'] = Hash::make($password);

        $registeredUser = $this->signUpRepository->create($data);

        // Only pass plain password if you need it in the event
        event(new UserRegistered($registeredUser, $password));

        return $registeredUser;
    }

    /**
     * Check if the user is already registered.
     *
     * @param array $data
     * @return bool
     */
    public function alreadyRegistered(array $data)
    {
        // Check if the user is already registered using the repository
        return $this->signUpRepository->exists($data['email'], $data['username']);
    }

    /**
     * Check if the user is already registered with a specific domain.
     *
     * @param array $data
     * @return bool
     */
    public function alreadyRegisteredDomain(array $data)
    {
        // Check if the user is already registered using the repository
        return $this->signUpRepository->alreadyRegisteredDomain($data);
    }
}
