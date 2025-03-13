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

class SignUpService
{
    private $signUpRepository;

    public function __construct(SignUpRepositoryInterface $signUpRepository)
    {
        $this->signUpRepository = $signUpRepository;
    }


    public function registerUser(array $data): User
    {
        $password = $data['password'];
        $data['password'] = bcrypt($password);

        // Step 1: Create the user using the repository
        $registeredUser = $this->signUpRepository->create($data);

        // Send the email to the registered user
        Mail::to($registeredUser->email)->send(new UserRegisteredMail($registeredUser, $password));

        // Optionally, do something else (e.g., return the created user)
        return $registeredUser;
    }
}
