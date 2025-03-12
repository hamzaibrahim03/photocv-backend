<?php

namespace App\Services;

use App\Repositories\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Collection;
use App\Events\LoginEvent ;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;


class AuthService
{
    private $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(array $credentials)
    {
        $user = $this->authRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials.', 401); // Unauthorized
        }

        if (!$user->is_active) {
            throw new \Exception('Your account is not active. Please contact support.', 403); // Forbidden
        }

        if (is_null($user->email_verified_at)) {
            throw new \Exception('Please verify your email before logging in.', 400); // Bad Request
        }

       // Update the last login timestamp
        $user->last_login = now();
        $user->save();

        // Generate token (for example, using Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }


    public function logout($user): void
    {
        $this->authRepository->revokeTokens($user);
    }


    public function forgotPassword(array $data): void
    {
        // Get user by email
        $user = $this->authRepository->findByEmail($data['email']);

        // Generate a random token
        $resetToken = Str::random(60); // You can customize the length

        // Store the token in the user's record
        $user->forgot_pass_token = $resetToken;
        $user->save();

        $reset_password_Url = route('reset-password', ['token' => $resetToken]);

        // Send the token to the user's email
        Mail::to($user->email)->send(new PasswordResetMail($reset_password_Url));
    }


    public function resetPassword(string $token, string $newPassword, string $confirmPassword): bool
    {
        // Check if the token exists and matches any user
        $user = User::where('forgot_pass_token', $token)->first();

        if (!$user || $newPassword !== $confirmPassword) {
            return false;
        }

        // Update the user's password and clear the token
        $user->password = bcrypt($newPassword);
        $user->forgot_pass_token = null; // Clear the reset token
        $user->save();

        return true;
    }



}
