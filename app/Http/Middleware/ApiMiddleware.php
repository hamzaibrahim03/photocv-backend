<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure the request accepts JSON
        if (!$request->isJson()) {
            //return response()->json(['error' => 'Accept header must be application/json'], 406);
        }

        /*
        // Authorization check: Ensure there's a Bearer token
        if (!$request->hasHeader('Authorization')) {
            return response()->json(['error' => 'Authorization header missing'], 401);
        }

        // Optionally: Validate token (use JWT or Laravel Passport here)
        $token = $request->bearerToken();
        if (!$token || !Auth::guard('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        */
        // Proceed with the request
        return $next($request);
    }
}

?>