<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Hanya cek untuk company role
        if ($user && $user->role === 'company') {
            if (!$user->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your company account is pending approval. Please wait for admin to approve your account.',
                    'data' => [
                        'is_approved' => false,
                        'status' => 'pending_approval'
                    ]
                ], 403);
            }
        }

        return $next($request);
    }
}
