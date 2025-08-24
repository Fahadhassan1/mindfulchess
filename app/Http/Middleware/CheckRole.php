<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $roles = explode('|', $role);
        $userRoles = auth()->user()->roles->pluck('name')->toArray();
        
        // Check if user has any of the required roles
        if (!empty(array_intersect($roles, $userRoles))) {
            return $next($request);
        }
        
        return redirect('/dashboard')->with('error', 'You do not have permission to access this page.');
    }
}
