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


    public function createParent(array $data): User
    {
        // Step 1: Generate a random alphanumeric password of 8 characters
        $randomPassword = Str::random(8); // Generates a random 8-character alphanumeric string

        // Step 2: Hash the password
        $hashedPassword = Hash::make($randomPassword);

        // Step 3: Add the hashed password to the data array
        $data['password'] = $hashedPassword;

        $parent_role = Role::where('name', 'Parent')->first();

        $data['role_id'] = $parent_role->id;

        // Step 4: Generate a random token for email verification
        $emailVerificationToken = Str::random(32); // Random 32-character alphanumeric string
        $data['email_varified_token'] = $emailVerificationToken;

        // Step 1: Create the user using the repository
        $parent_user = $this->signUpRepository->create($data);

        // Step 2: Dispatch the ParentRegistered event
        Event::dispatch(new ParentRegistered($parent_user));

        // Send the email to the parent user
        $verificationUrl = route('email-verified', ['token' => $emailVerificationToken]);
        Mail::to($parent_user->email)->send(new UserRegisteredMail($parent_user, $randomPassword, $verificationUrl));

        // Optionally, do something else (e.g., return the created user)
        return $parent_user;
    }




}
