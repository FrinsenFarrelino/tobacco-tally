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
use App\Models\AccessMenu;
use App\Models\Menu;
use App\Models\UserGroup;
use App\Models\Warehouse;
use Carbon\Carbon;

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
        // dd($formData);
        $formData['title'] = 'Home';

        // todo tambahkan kondisi jika usergroup name nya manager, maka langsung redirect ke dashboard grafik
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
            $list_menu = Menu::where('is_active', 1)->orderBy("order", "asc")->get();
            Session::put('list_menu', $list_menu);
            $userGroup = UserGroup::where('id', Session::get('user')['user_group_id'])->first();
            Session::put('user_group', $userGroup);
            $getAccessMenu = AccessMenu::where('user_group_id', $userGroup->id)
                ->with(['menu' => function ($query) {
                    $query->select('id', 'code');
                }])
                ->select('access_menus.id', 'access_menus.user_group_id', 'access_menus.menu_id', 'open', 'add', 'edit', 'delete', 'print', 'approve', 'disapprove', 'menus.url_menu as menu_url')
                ->distinct('access_menus.menu_id')
                ->orderBy('access_menus.menu_id')
                ->orderBy('open', 'DESC')
                ->orderBy('add', 'DESC')
                ->orderBy('edit', 'DESC')
                ->orderBy('delete', 'DESC')
                ->orderBy('print', 'DESC')
                ->orderBy('approve', 'DESC')
                ->orderBy('disapprove', 'DESC')
                ->leftJoin('menus', 'access_menus.menu_id', '=', 'menus.id')
                ->get();
            Session::put('access_menu', $getAccessMenu);

            // update overstaple status
            $this->updateOverstapleStatus();

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

        if ($request->session()->get('message')) {
            $message = $request->session()->get('message');
        }

        Session::flush();
        Auth::logout();
        return redirect()->route('login')
            ->with('success', $message);
    }

    public function updateOverstapleStatus() {
        $dataWarehouses = Warehouse::all();
        foreach ($dataWarehouses as $dataWarehouse) {
            if ($this->isMoreThanThreeMonths($dataWarehouse->overstapled_at)) {
                Warehouse::where('id', $dataWarehouse->id)->update([
                    'is_overstapled' => false
                ]);
            }
        }
    }

    public function isMoreThanThreeMonths($stockLastUpdated)
    {
        $dateToCheck = Carbon::parse($stockLastUpdated);

        // Get the current date
        $currentDate = Carbon::now();

        // Check if the date is more than 3 months ago
        if ($dateToCheck->diffInMonths($currentDate) > 3) {
            // Date is more than 3 months ago
            return true;
        } else {
            // Date is within the last 3 months
            return false;
        }
    }
}
