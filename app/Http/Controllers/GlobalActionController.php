<?php

namespace App\Http\Controllers;

class GlobalActionController extends Controller
{
    // CONVERT FROM ACTIONS TO MODEL

    protected $actionsToModel = [
        // GET MASTER
        'getProvince' => 'Province',
        'getCity' => 'City',
        'getSubdistrict' => 'Subdistrict',
        'getWarehouse' => 'Warehouse',
        'getBranch' => 'Branch',
        'getCustomer' => 'Customer',
        'getSupplier' => 'Supplier',
        'getEmployee' => 'Employee',
        'getItem' => 'Item',
        'getCategory' => 'Category',
        'getUnit' => 'Unit',
        'getPriceList' => 'PriceList',
        'getType' => 'Type',

        // ADD MASTER
        'addProvince' => 'Province',
        'addCity' => 'City',
        'addSubdistrict' => 'Subdistrict',
        'addWarehouse' => 'Warehouse',
        'addBranch' => 'Branch',
        'addCustomer' => 'Customer',
        'addSupplier' => 'Supplier',
        'addEmployee' => 'Employee',
        'addItem' => 'Item',
        'addCategory' => 'Category',
        'addUnit' => 'Unit',
        'addPriceList' => 'PriceList',
        'addType' => 'Type',

        // UPDATE
        'updateProvince' => 'Province',
        'updateCity' => 'City',
        'updateSubdistrict' => 'Subdistrict',
        'updateWarehouse' => 'Warehouse',
        'updateBranch' => 'Branch',
        'updateCustomer' => 'Customer',
        'updateSupplier' => 'Supplier',
        'updateEmployee' => 'Employee',
        'updateItem' => 'Item',
        'updateCategory' => 'Category',
        'updateUnit' => 'Unit',
        'updatePriceList' => 'PriceList',
        'updateType' => 'Type',       

        // SOFT DELETES
        'sofDeleteProvince' => 'Province',
        'sofDeleteCity' => 'City',
        'sofDeleteSubdistrict' => 'Subdistrict',
        'sofDeleteWarehouse' => 'Warehouse',
        'softDeleteBranch' => 'Branch',
        'sofDeleteCustomer' => 'Customer',
        'sofDeleteSupplier' => 'Supplier',
        'sofDeleteEmployee' => 'Employee',
        'sofDeleteItem' => 'Item',
        'sofDeleteCategory' => 'Category',
        'sofDeleteUnit' => 'Unit',
        'sofDeletePriceList' => 'PriceList',
        'sofDeleteType' => 'Type',
    ];


    public function getActionsToModel()
    {
        return $this->actionsToModel;
    }
}
