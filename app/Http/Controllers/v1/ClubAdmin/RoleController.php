<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::select('id', 'name')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Roles fetched successfully',
            'data'    => $roles
        ], 200);
    }
}
