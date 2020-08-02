<?php

namespace App\Http\Controllers;
use Auth;
use Validator;
use App\Models\User;
use App\Traits\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use Token;
    /**
     * Login with token
     * social Login
     */
    public function login(Request $request) {
        $request->validate([
            'token'     => 'required',
            'type'      => 'required',
        ]);
        try {
            $token = $request->token;
            if($request->type == 'facebook') {
                $social = Socialite::driver($request->type)
                    ->fields([
                        'name', 
                        'first_name', 
                        'last_name', 
                        'email'
                    ])->userFromToken($token);
            }
            else if($request->type == 'google') {
                $social = Socialite::driver($request->type)->userFromToken($token);
            }

            return ['data' => $social];

            if($social) {

                $name       = $this->_getName($social, $request->type);
                $email      = $social->getEmail();
                $socialId   = $social->getId();

                $user = User::where('social_id', $socialId)
                    ->where('login_type', $request->type)
                    ->get()
                    ->first();
                if(!$user) {
                    $user                   = new User;
                    $user->email            = $email;
                    $user->social_id        = $socialId;
                    $user->login_type       = $request->type;
                    $user->remember_token   = $this->createToken();
                }
                
                $user->name         = sprintf("%s %s", $name['first_name'], $name['last_name']);
                $user->image_url    = $social->avatar;
                $temporaryPassword  = $this->createToken();
                $user->password     = Hash::make($temporaryPassword);
                $user->save();

                $credentials = [
                    'social_id' => $social->id,
                    'login_type'=> $request->type,
                    'password'  => $temporaryPassword,
                ];
                if($token = $this->guard()->attempt($credentials)) {
                    return response()
                    ->json(['status' => 'success'])
                    ->header('Authorization', $token);
                }
                return response()->json(['status' => 'error', 'error' => 'Invalid Token'], 401);
            }
        }
        catch(\Exception $exception) {
            return response()->json(['status' => 'error', 'error' =>  $exception->getMessage()], 401);
        }
    }

    /**
    * Logout User
    */
    public function logout()
    {
        $this->guard()->logout();
        return response()->json([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
    }

    /**
    * Get authenticated user
    */
    public function user(Request $request)
    {
        $user = User::find(Auth::user()->id);
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    /**
    * Refresh JWT token
    */
    public function refresh()
    {   
        try{
            if ($token = $this->guard()->refresh()) {
                return response()
                    ->json(['status' => 'successs'], 200)
                    ->header('Authorization', $token);
            }
            return response()->json(['error' => 'refresh_token_error'], 401);
        }
        catch(\Exception $exception) {
            return response()->json(['error' =>  $exception->getMessage()], 401);
        }
    }

    /**
    * Return auth guard
    */
    private function guard()
    {
        return Auth::guard();
    }

     /**
     * get user first name and last name;
     */
    private function _getName($providerUser, $type) {
        $result = [];
        switch($type){
            case 'facebook':
                $result['first_name'] = $providerUser->offsetGet('first_name');
                $result['last_name']  = $providerUser->offsetGet('last_name');
                break;

            case 'google':
                $result['first_name'] = $providerUser->offsetGet('given_name');
                $result['last_name']  = $providerUser->offsetGet('family_name');
                break;

            default:
                $result['first_name'] = $providerUser->getName();
                $result['last_name']  = $providerUser->getName();
        }

        return $result;
    }

}