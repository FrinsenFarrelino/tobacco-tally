<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRegisterRequest;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\AccessMenu;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $checkUser = User::where('email', $request->email)->first();

            if ($checkUser === null) {
                return $this->sendResponse(false, 600);
            }
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

                $checkUser->increment('login_attempt');

                if ($checkUser->login_attempt > 2) {
                    User::where('username', $request->username)->update([
                        'is_blocked' => 1,
                        'remark' => 'blocked due to wrong password 3 times'
                    ]);
                    return $this->sendResponse(false, 700);
                }

                return $this->sendResponse(false, 601);
            }

            $user = Auth::user();

            // dd($user);

            $getUser = array('id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'user_group' => $user->user_groups->name);

            // reset login attempt
            User::where('username', $user->username)->update([
                'login_attempt' => 0
            ]);

            $data = array('token' => $token, 'user' => $getUser);
            return $this->sendResponse(true, Response::HTTP_OK, $data);
        } catch (\Exception $e) {
            return $this->errorHandle($e, $request);
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->sendResponse(true, Response::HTTP_OK, ['message' => 'Success Logout']);
    }
}
