<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\LoginRequest;

class DashboardController extends Controller
{
    private $globalVariable;

    // public function __construct(GlobalVariable $globalVariable)
    // {
    //     $this->globalVariable = $globalVariable;
    //     $this->globalVariable->ModuleGlobal('home', 'dashboard', '/', 'dashboard', 'dashboard');
    // }

    public function dashboard(Request $request)
    {
        $listMenu = Session::get('list_menu');
        $formData['list_menus'] = $listMenu;

        return view('dashboard', $formData);
    }

    public function loginpage()
    {
        return view('auth.login', [
            'title' => 'Login'
        ]);
    }

    /**
     * loginSend a newly created resource in storage.
     */
    public function loginSend(Request $request)
    {
        try {
            $checkUser = User::where('email', $request->email)->first();

            if ($checkUser === null) {
                return redirect()->back()
                    ->with('error', 'Your credential does not match out record')
                    ->withInput();
            }

            if ($checkUser->is_blocked) {
                return redirect()->back()
                    ->with('error', 'Blocked due to wrong password 3 times')
                    ->withInput();
            }

            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

                $checkUser->increment('login_attempt');

                if ($checkUser->login_attempt > 2) {
                    User::where('email', $request->email)->update([
                        'is_blocked' => 1,
                        'remark' => 'blocked due to wrong password 3 times'
                    ]);
                    return redirect()->back()
                        ->with('error', 'Blocked due to wrong password 3 times')
                        ->withInput();
                }

                return redirect()->back()
                    ->with('error', 'Wrong email or password')
                    ->withInput();
            }

            $user = Auth::user();
            Session::put('user', $user);
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect()->back()
                    ->withErrors($e)
                    ->with('error', $e->getMessage())
                    ->withInput();
        }
    }

    public function sendLogout(Request $request)
    {
        $message = 'Successfully logged out';

        if($request->session()->get('message')){
            $message = $request->session()->get('message');
        }

        Session::flush();
        Auth::logout();
        return redirect()->route('login')
            ->with('success', $message);
    }
}
