<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Hanya cek untuk user role (job seeker)
        if ($user && $user->role === 'user') {
            if (!$user->profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete your profile first before applying for jobs.',
                    'data' => [
                        'has_profile' => false,
                        'action_required' => 'complete_profile'
                    ]
                ], 403);
            }
        }

        return $next($request);
    }
}
