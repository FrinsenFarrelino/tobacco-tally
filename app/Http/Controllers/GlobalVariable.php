<?php

namespace App\Http\Controllers;

class GlobalVariable
{
    public $module;
    public $subModule;
    public $menu;
    public $menuUrl;
    public $menuRoute;
    public $menuParam;

    public $actionGetMenu;

    // MASTER
    public $actionGetProvince;
    public $actionGetCity;
    public $actionGetSubdistrict;
    public $actionGetWarehouse;
    public $actionGetBranch;
    public $actionGetBank;
    public $actionGetCustomer;
    public $actionGetSupplier;
    public $actionGetEmployee;
    public $actionGetDivision;
    public $actionGetPosition;
    public $actionGetItem;
    public $actionGetType;
    public $actionGetCategory;
    public $actionGetPriceList;
    public $actionGetUnit;

    // TRANSACTION
    public $actionGetPurchase;
    public $actionGetSale;
    public $actionGetOutgoingItem;
    public $actionGetIncomingItem;

    // REPORT
    public $actionGetStockReport;

    // SETTING
    public $actionGetUser;
    public $actionGetUserGroup;
    public $actionGetTax;

    public function __construct()
    {
        $this->actionGetMenu = 'getMenu';
        
        // master-data
        $this->actionGetProvince = 'getProvince';
        $this->actionGetWarehouse = 'getWarehouse';
        $this->actionGetSubdistrict = 'getSubdistrict';
        $this->actionGetBranch = 'getBranch';
        $this->actionGetCity = 'getCity';
        $this->actionGetItem = 'getItem';
        $this->actionGetUnit = 'getUnit';
        $this->actionGetPriceList = 'getPriceList';
        $this->actionGetCategory = 'getCategory';
        $this->actionGetType = 'getType';
        $this->actionGetBank = 'getBank';
        $this->actionGetCustomer = 'getCustomer';
        $this->actionGetSupplier = 'getSupplier';
        $this->actionGetEmployee = 'getEmployee';
        $this->actionGetDivision = 'getDivision';
        $this->actionGetPosition = 'getPosition';

        // transaction
        $this->actionGetPurchase = 'getPurchase';
        $this->actionGetSale = 'getSale';
        $this->actionGetOutgoingItem = 'getOutgoingItem';
        $this->actionGetIncomingItem = 'getIncomingItem';

        // report
        $this->actionGetStockReport = 'getStockReport';

        // setting
        $this->actionGetUser = 'getUser';
        $this->actionGetUserGroup = 'getUserGroup';
        $this->actionGetTax = 'getTax';
    }

    public function ModuleGlobal($module, $subModule, $menuUrl, $menuRoute, $menuParam)
    {
        // Initialize your global properties in the constructor
        $this->module = $module;
        $this->subModule = $subModule;
        $this->menuUrl = $menuUrl;
        $this->menuRoute = $menuRoute;
        $this->menuParam = $menuParam;
    }
}
