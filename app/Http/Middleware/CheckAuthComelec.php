<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckAuthComelec
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        $token = $request->session()->get('student_number');

        try {
            // If not yet logged out
            if ($token) { 
                $user_role = $request->session()->get('user_role');

                if ($user_role !== 'comelec') {
                    return redirect('organization/elections');
                }
            }
            else {
                // If token was expired, not logged out
                return redirect()->route('view.login')->with('token_invalid', 'Your authentication token has expired. Please login again.');
            }
        } 
        catch(Exception $e) {

        }
    
        return $next($request);
    }
}
