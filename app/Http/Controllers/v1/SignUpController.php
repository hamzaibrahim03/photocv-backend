<?php

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;

use App\Services\SignUpService;
use App\Repositories\SignUpRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Responses\SignUpResponse;
use App\Events\ParentRegistered;
use Illuminate\Support\Facades\DB;
use App\Models\User;



class SignUpController extends Controller
{

    private $signUpRepository;
	private $signUpService;


	public function __construct(SignUpService $signUpService)
    {
        $this->signUpService = $signUpService;
    }

    public function store(SignUpRequest $request)
    {
        try
        {
            DB::beginTransaction();
            // Pass the validated data to the service to create the course
            $parent_user = $this->signUpService->registerUser($request->validated());

            DB::commit();

            // Return a success JSON response with the newly created course data
            return SignUpResponse::success('User registered successfully.', $parent_user, 201);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollback();
            // Return an error response if something goes wrong
            return SignUpResponse::error('Failed to register user.', $e->getMessage(), 500);
        }
    }

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
