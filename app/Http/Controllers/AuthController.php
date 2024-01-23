<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Comelec;
use App\Models\Organization;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthController extends Controller
{    
    public function viewLogin()
    {
        return inertia('Login');
    }

    public function authComelecLogin(Request $request)
    {
        $cookie_minutes_lifetime = 300; // Expiry of the cookie that contains the jwt token

        $cookie_data = [
            'student_number' => $request->StudentNumber,
            'user_role' => 'comelec',
        ];

        $cookie_data = json_encode($cookie_data);

        $token = 'Authorized';
        $redirect = '/comelec/elections';

        // Put student number in a session
        //$request->session()->put('student_number', $request->StudentNumber);
               
        $user_info_cookie = cookie('user_info', $cookie_data, $cookie_minutes_lifetime);
        $cookie = cookie('jwt_token', $token, $cookie_minutes_lifetime);

        return response()->json(['redirect' => $redirect])->withCookie($cookie)->withCookie($user_info_cookie);
    } 

    public function authOfficerLogin(Request $request)
    {
        $cookie_minutes_lifetime = 300; // Expiry of the cookie that contains the jwt token
        $cookie_data = [
            'student_number' => $request->StudentNumber,
            'user_role' => 'organization',
        ];

        $cookie_data = json_encode($cookie_data);

        $token = 'Authorized';
        $redirect = '/organization/elections';

        // Put student number in a session
        //$request->session()->put('student_number', $request->StudentNumber);
               
        $user_info_cookie = cookie('user_info', $cookie_data, $cookie_minutes_lifetime, null, null, true, true, false, null);
        $cookie = cookie('jwt_token', $token, $cookie_minutes_lifetime, null, null, true, true, false, null);

        return response()->json(['redirect' => $redirect])->withCookie($cookie)->withCookie($user_info_cookie);
    } 

    public function logout(Request $request) {
        try { 
            // Instruct client side to delete the cookies with withCookie() and redirect to login page
            $cookie = cookie()->forget('jwt_token');
            $user_info_cookie = cookie()->forget('user_info');
            $logout_cookie = cookie('logout_pass', 'true', 1);

            return response()->json([
                'logout' => 'true',
            ])->withCookie($user_info_cookie)->withCookie($cookie)->withCookie($logout_cookie);
            
        }
        catch(Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    
      
    public function registerComelec(Request $request)
    {   
        try {
            Comelec::create([
                'StudentNumber' => $request->input('StudentNumber'),
                'Password' => Hash::make($request->input('Password')),
                'Position' => $request->input('Position'),
            ]);

            return response()->json(['message' => 'Comelec successfully inserted.']);
        }
        catch(Exception $e) {
            return response()->json(['Something went wrong:' => $e->getMessage()]);
        }
    }

    public function registerOrganization(Request $request)
    {   
        try {
            Organization::create([
                'StudentNumber' => $request->input('StudentNumber'),
                'OfficerPositionId' => $request->input('OfficerPositionId'),
                'OrganizationName' => $request->input('OrganizationName'),
                'Password' => Hash::make($request->input('Password')),
            ]);

            return response()->json(['message' => 'Organization member successfully inserted.']);
        }
        catch(Exception $e) {
            return response()->json(['Something went wrong:' => $e->getMessage()]);
        }
    }
}
