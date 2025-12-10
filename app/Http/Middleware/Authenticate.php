<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Override handle to check user toggle status
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        // ✅ Check user status
        $user = Auth::user();
        if ($user && $user->toggle == 0) {
            $user->currentAccessToken()->delete();  // logout user

            return response()->json([
                'message' => 'Your account has been deactivated. Please check your email for details or contact the administrator for further assistance.'
            ], 403);
        }

        return $next($request);
    }
}
