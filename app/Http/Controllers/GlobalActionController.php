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
        'getBank' => 'Bank',
        'getCustomer' => 'Customer',
        'getCustomerBankAccountGrid' => 'Customer',
        'getSupplier' => 'Supplier',
        'getEmployee' => 'Employee',
        'getPosition' => 'Position',
        'getDivision' => 'Division',
        'getItem' => 'Item',
        'getCategory' => 'Category',
        'getUnit' => 'Unit',
        'getPriceList' => 'PriceList',
        'getType' => 'Type',

        // GET TRANSACTION
        'getPurchase' => 'Purchase',
        'getPurchaseItemGrid' => 'Purchase',
        'getPurchaseDetail' => 'PurchaseItemDetail',
        'getSale' => 'Sale',
        'getSaleItemGrid' => 'Sale',
        'getSaleDetail' => 'SaleItemDetail',
        'getIncomingItem' => 'StockTransfer',
        'getIncomingItemItemGrid' => 'StockTransfer',
        'getOutgoingItem' => 'StockTransfer',
        'getOutgoingItemItemGrid' => 'StockTransfer',

        // GET REPORT
        'getStockReport' => 'StockReport',

        // GET SETTING
        'getUser' => 'User',
        'getUserGroup' => 'UserGroup',
        'getAccessMenu' => 'AccessMenu',
        'getTax' => 'Tax',

        // ADD MASTER
        'addProvince' => 'Province',
        'addCity' => 'City',
        'addSubdistrict' => 'Subdistrict',
        'addWarehouse' => 'Warehouse',
        'addBranch' => 'Branch',
        'addBank' => 'Bank',
        'addCustomer' => 'Customer',
        'addSupplier' => 'Supplier',
        'addEmployee' => 'Employee',
        'addPosition' => 'Position',
        'addDivision' => 'Division',
        'addItem' => 'Item',
        'addCategory' => 'Category',
        'addUnit' => 'Unit',
        'addPriceList' => 'PriceList',
        'addType' => 'Type',

        // ADD TRANSACTION
        'addPurchase' => 'Purchase',
        'addSale' => 'Sale',
        'addOutgoingItem' => 'StockTransfer',

        // ADD SETTING
        'addUser' => 'User',
        'addUserGroup' => 'UserGroup',

        // UPDATE
        'updateProvince' => 'Province',
        'updateCity' => 'City',
        'updateSubdistrict' => 'Subdistrict',
        'updateWarehouse' => 'Warehouse',
        'updateBranch' => 'Branch',
        'updateBank' => 'Bank',
        'updateCustomer' => 'Customer',
        'updateSupplier' => 'Supplier',
        'updateEmployee' => 'Employee',
        'updatePosition' => 'Position',
        'updateDivision' => 'Division',
        'updateItem' => 'Item',
        'updateCategory' => 'Category',
        'updateUnit' => 'Unit',
        'updatePriceList' => 'PriceList',
        'updateType' => 'Type',
        
        // UPDATE TRANSACTION
        'updatePurchase' => 'Purchase',
        'updateStatusPurchase' => 'Purchase',
        'updateSale' => 'Sale',
        'updateStatusSale' => 'Sale',
        'updateStatusIncomingItem' => 'StockTransfer',
        'updateOutgoingItem' => 'StockTransfer',
        'updateStatusOutgoingItem' => 'StockTransfer',

        // UPDATE SETTING
        'updateUser' => 'User',
        'updateUserGroup' => 'UserGroup',
        'updateTax' => 'Tax',

        // SOFT DELETES
        'softDeleteProvince' => 'Province',
        'softDeleteCity' => 'City',
        'softDeleteSubdistrict' => 'Subdistrict',
        'softDeleteWarehouse' => 'Warehouse',
        'softDeleteBranch' => 'Branch',
        'softDeleteBank' => 'Bank',
        'softDeleteCustomer' => 'Customer',
        'softDeleteSupplier' => 'Supplier',
        'softDeleteEmployee' => 'Employee',
        'softDeletePosition' => 'Position',
        'softDeleteDivision' => 'Division',
        'softDeleteItem' => 'Item',
        'softDeleteCategory' => 'Category',
        'softDeleteUnit' => 'Unit',
        'softDeletePriceList' => 'PriceList',
        'softDeleteType' => 'Type',

        // SOFT DELETE TRANSACTION
        'softDeletePurchase' => 'Purchase',
        'softDeleteSale' => 'Sale',
        'softDeleteOutgoingItem' => 'StockTransfer',

        // SOFT DELETE SETTING
        'softDeleteUser' => 'User',
        'softDeleteUserGroup' => 'UserGroup',
    ];


    public function getActionsToModel()
    {
        return $this->actionsToModel;
    }
}
