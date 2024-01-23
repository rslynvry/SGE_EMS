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

        $request->session()->put('student_number', $request->StudentNumber);
        $request->session()->put('user_role', 'comelec');

        // Put student number in a session
        //$request->session()->put('student_number', $request->StudentNumber);
               
        //$user_info_cookie = cookie('user_info', $cookie_data, $cookie_minutes_lifetime);
        //$cookie = cookie('jwt_token', $token, $cookie_minutes_lifetime);

        //return response()->json(['redirect' => $redirect])->withCookie($cookie)->withCookie($user_info_cookie);

        return response()->json(['redirect' => $redirect]);
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

            $request->session()->forget('student_number');
            $request->session()->forget('user_role');
            return response()->json([
                'logout' => 'true',
            ]);
            
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
