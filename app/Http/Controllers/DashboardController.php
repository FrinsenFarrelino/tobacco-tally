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

class DashboardController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalVariable = $globalVariable;
        $this->globalActionController = $globalActionController;
        $this->globalVariable->ModuleGlobal('home', 'dashboard', '/', 'dashboard', 'dashboard');
    }

    public function dashboard(Request $request)
    {
        $listMenu = Session::get('list_menu');
        $user_group = Session::get('user_group');

        $formData['list_menus'] = $listMenu;
        // dd($formData);
        $formData['title'] = 'Home';

        // data purchase section
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetPurchase);
        $result = $this->getData($set_request);
        $totalPurchaseNotApproved = 0;
        $totalPurchaseApproved = 0;

        foreach ($result['data'] as $dataPurchase) {
            if($dataPurchase['is_approve'] !== true) {
                $totalPurchaseNotApproved++;
            } else {
                $totalPurchaseApproved++;
            }
        }
        $formData['totalPurchaseNotApproved'] = $totalPurchaseNotApproved;
        $formData['totalPurchaseApproved'] = $totalPurchaseApproved;
        // end of data purchase section

        // data sale section
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetSale);
        $result = $this->getData($set_request);
        $totalSaleNotApproved = 0;
        $totalSaleApproved = 0;

        foreach ($result['data'] as $dataSale) {
            if($dataSale['is_approve'] !== true) {
                $totalSaleNotApproved++;
            } else {
                $totalSaleApproved++;
            }
        }
        $formData['totalSaleNotApproved'] = $totalSaleNotApproved;
        $formData['totalSaleApproved'] = $totalSaleApproved;
        // end of data sale section

        // data warehouse section
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetOutgoingItem);
        $result = $this->getData($set_request);
        $totalOutgoing = 0;
        $totalIncoming = 0;
        $totalOnTheWay = 0;

        foreach ($result['data'] as $dataOutgoing) {
            if($dataOutgoing['is_approve_1'] == true && $dataOutgoing['is_approve_2'] == false) {
                $totalOnTheWay++;
            }
            if($dataOutgoing['is_approve_1'] == true && $dataOutgoing['is_approve_2'] == true) {
                $totalIncoming++;
            }
            if($dataOutgoing['is_approve_1'] == false && $dataOutgoing['is_approve_2'] == false) {
                $totalOutgoing++;
            }
        }
        $formData['totalOutgoing'] = $totalOutgoing;
        $formData['totalIncoming'] = $totalIncoming;
        $formData['totalOnTheWay'] = $totalOnTheWay;
        // end of data warehouse section

        // data stock
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetWarehouse);
        $result = $this->getData($set_request);
        $formData['dataWarehouses'] = $result['data'];
        // end of data stock

        // admin lombok
        if ($user_group['name'] === 'Admin Lombok' && $user_group['branch_id'] === 1) {
            return view('dashboard-adm-lombok', $formData);
        }
        // admin bojonegoro
        if ($user_group['name'] === 'Admin Bojonegoro' && $user_group['branch_id'] === 2) {
            return view('dashboard-adm-bjn', $formData);
        }
        // admin warehouse
        if (str_contains($user_group['name'], 'Warehouse')) {
            return view('dashboard-adm-wh', $formData);
        }
        // super admin
        if($user_group['name'] === 'Admin') {
            return view('dashboard-manager', $formData);
        }
        // manager
        if (str_contains($user_group['name'], 'Manager')) {
            return view('dashboard-manager', $formData);
        }

        return redirect()->route('error')->with('message', 'You do not have permission to access this page.');

    }
}
