<?php

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;

use App\Services\SignUpService;
use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Responses\SignUpResponse;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Database\QueryException;


class SignUpController extends Controller
{
	private $signUpService;

	public function __construct(SignUpService $signUpService)
    {
        $this->signUpService = $signUpService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SignUpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $registeredUser = $this->signUpService->registerUser($request->all());

            DB::commit();

            return SignUpResponse::success('User registered successfully.', $registeredUser, 201);
        } catch (QueryException $e) {
            DB::rollBack();

            // Check for duplicate entry error (error code 1062)
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate entry')) {
                if (str_contains($e->getMessage(), 'users_username_unique')) {
                    return SignUpResponse::error('Username already exists. Please choose a different one.', null, 409);
                }
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    return SignUpResponse::error('Email address is already in use.', null, 409);
                }
            }

            return SignUpResponse::error('Failed to register user.', $e->getMessage(), 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return SignUpResponse::error('An unexpected error occurred.', $e->getMessage(), 500);
        }
    }


    /**
     * Check if the user is already registered.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function alreadyRegistered(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email',
            'username' => 'required|string|max:255',
        ]);

        return $this->signUpService->alreadyRegistered($request->only(['email', 'username']));
    }

    /**
     * Check if the domain is already registered.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function alreadyRegisteredDomain(Request $request)
    {
        // Validate the request data
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        return $this->signUpService->alreadyRegisteredDomain($request->only(['domain']));
    }

    /**
     * Verify the user's email address.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function EmailVerification(Request $request)
    {
        // Validate the token
        $token = $request->query('token');
        $user = User::where('email_varified_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification token.'], 400);
        }

        // Update email_verified_at column
        $user->email_verified_at = now();
        $user->email_varified_token = null; // Clear the token
        $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

}
