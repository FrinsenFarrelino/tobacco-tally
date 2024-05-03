<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;
use Illuminate\Support\Facades\Session;

class StockBalanceController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    private $index_file;
    private $index1_file;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal(module: 'report', menuParam: 'stock_balance', subModule: 'report_stock_balance', menuRoute: 'stock-balance', menuUrl: 'report/stock-balance');

        $this->index_file = 'report.stock_balance.index';
        $this->index1_file = 'report.stock_balance.index1';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $generate_nav_button = generateNavbutton([], 'reload', 'index', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'index');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['action'] = $this->globalVariable->actionGetWarehouse;
        $formData['menu_route'] = $this->globalVariable->menuRoute;
        $formData['menu_param'] = $this->globalVariable->menuParam;

        $user_group = Session::get('user_group');

        // update overstaple status
        $this->updateOverstapleStatus();
        
        if (str_contains($user_group['name'], 'Warehouse') || $user_group['name'] === 'Admin') {
            return view($this->index1_file, $formData);
        }

        return view($this->index_file, $formData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }
}
