<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\v1\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use App\Repositories\AuthRepositoryInterface;
use App\Http\Responses\AuthResponse;
use App\Events\LoginEvent;

class AuthController extends Controller
{
    //
    private $authRepository;
	private $authService;


	public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(AuthRequest $request)
    {
        try {
            // Pass the validated data to the service to login
            $result = $this->authService->login($request->validated());

            // Return a success JSON response with the logged-in user data
            return AuthResponse::success('Login successful.', $result, 200);
        } catch (\Exception $e) {
            // Handle exceptions and return an error response
            return AuthResponse::error($e->getMessage(), $e->getCode() ? 501: 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return AuthResponse::success('Logout successful.', null, 200);
        } catch (\Exception $e) {
            return AuthResponse::error('Failed to logout.', $e->getMessage(), 500);
        }
    }


    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            // Pass validated data to the service to handle the password reset logic
            $resetUrl = $this->authService->forgotPassword($request->validated());

            // Dev convenience only: surface the link directly since local
            // mail is just written to the log, not actually delivered.
            // Never include this outside local - it would let anyone with
            // an email address skip the mail step entirely.
            $data = app()->environment('local') ? ['dev_reset_url' => $resetUrl] : null;

            return AuthResponse::success('Password reset link has been sent to your email.', $data, 200);
        } catch (\Exception $e) {
            return AuthResponse::error('Failed to send password reset link.', $e->getMessage(), 500);
        }
    }

    public function resetPassword( ResetPasswordRequest $request)
    {
        try {
            $token = $request->validated('token');
            $isSuccessful = $this->authService->resetPassword($token, $request->new_password, $request->new_password_confirmation);

            if ($isSuccessful) {
                return AuthResponse::success('Your password has been successfully reset.', null, 200);
            }

            return AuthResponse::error('Invalid token or password mismatch.', null, 400);
        } catch (\Exception $e) {
            return AuthResponse::error('Failed to reset password.', $e->getMessage(), 500);
        }
    }

}
