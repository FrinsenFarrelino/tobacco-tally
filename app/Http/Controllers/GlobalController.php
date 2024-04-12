<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Services\CustomerGridService;
use App\Http\Services\PurchaseGridService;
use App\Http\Services\SaleGridService;
use App\Http\Services\StockTransferGridService;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchaseItemDetail;
use App\Models\StockBalance;
use App\Models\StockReport;
use App\Models\StockTransfer;
use App\Models\StockTransferItemDetail;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GlobalController extends Controller
{
    private $globalVariable;
    protected $customerGridService;
    protected $purchaseGridService;
    protected $saleGridService;
    protected $stockTransferGridService;

    public function __construct(
        GlobalActionController $globalActionController,
        CustomerGridService $customerGridService,
        PurchaseGridService $purchaseGridService,
        SaleGridService $saleGridService,
        StockTransferGridService $stockTransferGridService,
    ) {
        $this->globalActionController = $globalActionController;
        $this->customerGridService = $customerGridService;
        $this->purchaseGridService = $purchaseGridService;
        $this->saleGridService = $saleGridService;
        $this->stockTransferGridService = $stockTransferGridService;
    }

    public function requestGetData(Request $request)
    {
        $set_request = SetRequestGlobal(action: $request->action, filter: $request->filters, requestData: $request);

        if (isset($set_request['columnHead'])) {
            unset($set_request['columnHead']);
        }
        if (isset($set_request['requestData']['action'])) {
            unset($set_request['requestData']['action']);
        }
        $result = $this->getData($set_request);
        return response()->json($result);
    }

    public function getData($request)
    {
        $action = $request['action'];
        $actionsToModel = $this->globalActionController->getActionsToModel();
        if (!array_key_exists($action, $actionsToModel)) {
            return response()->json(['success' => false, 'message' => 'Action not found'], 404);
        }

        $filters = $request['filters'];

        // untuk grid
        if (isset($request['requestData']['columnHead'])) {
            $columnHead = $request['requestData']['columnHead'];
        }

        $query = $this->modelName($actionsToModel[$action])::query();

        $modelClass = $this->modelName($actionsToModel[$action]);
        $modelInstance = new $modelClass;
        $tableName = $modelInstance->getTable();

        // Filter by search
        if (isset($request['search'])) {
            $query->where(function ($query) use ($request) {
                foreach ($request['search'] as $search) {
                    if ($search['term'] == 'like') {
                        $searchTerm = '%' . $search['query'] . '%';

                        // Split the search query into individual words
                        $words = explode(' ', $search['query']);

                        // Reverse the order of characters in each word
                        $reversedWords = array_map('strrev', $words);

                        // Join the reversed words to create a reversed search term
                        $reversedSearchTerm = '%' . implode(' ', $reversedWords) . '%';

                        $query->orWhere(function ($query) use ($search, $searchTerm, $reversedSearchTerm) {
                            $query->where($search['key'], 'ilike', $searchTerm)
                                ->orWhere($search['key'], 'ilike', $reversedSearchTerm);
                        });
                    } elseif ($search['term'] == 'equal') {
                        $query->orWhere($search['key'], '=', $search['query']);
                    } elseif ($search['term'] == 'not equal') {
                        $query->orWhere($search['key'], '!=', $search['query']);
                    }
                }
            });
        }

        if ($action == 'getCity') {
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'cities.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'cities.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'cities.deleted_by');
            $query->select(
                'cities.*',
                'provinces.name as province_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getSubdistrict') {
            $query->leftJoin('cities', 'cities.id', '=', 'subdistricts.city_id');
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'subdistricts.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'subdistricts.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'subdistricts.deleted_by');
            $query->select(
                'subdistricts.*',
                'cities.name as city_name',
                'provinces.name as province_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getWarehouse') {
            $query->leftJoin('branches', 'branches.id', '=', 'warehouses.branch_id');
            $query->leftJoin('items', 'items.id', '=', 'warehouses.item_id');
            $query->leftJoin('categories', 'categories.id', '=', 'items.category_id');
            $query->leftJoin('units', 'units.id', '=', 'items.unit_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'warehouses.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'warehouses.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'warehouses.deleted_by');
            $query->select(
                'warehouses.*',
                'branches.name as branch_name',
                'items.name as item_name',
                'items.code as item_code',
                'categories.name as category_name',
                'units.name as unit_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getEmployee') {
            $query->leftJoin('positions', 'positions.id', '=', 'employees.position_id');
            $query->leftJoin('divisions', 'divisions.id', '=', 'employees.division_id');
            $query->leftJoin('subdistricts', 'subdistricts.id', '=', 'employees.subdistrict_id');
            $query->leftJoin('cities', 'cities.id', '=', 'subdistricts.city_id');
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'employees.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'employees.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'employees.deleted_by');
            $query->select(
                'employees.*',
                'positions.name as position_name',
                'divisions.name as division_name',
                'subdistricts.name as subdistrict_name',
                'cities.name as city_name',
                'provinces.name as province_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getSupplier') {
            $query->leftJoin('subdistricts', 'subdistricts.id', '=', 'suppliers.subdistrict_id');
            $query->leftJoin('cities', 'cities.id', '=', 'subdistricts.city_id');
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'suppliers.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'suppliers.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'suppliers.deleted_by');
            $query->select(
                'suppliers.*',
                'subdistricts.name as subdistrict_name',
                'cities.name as city_name',
                'provinces.name as province_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getCustomer') {
            $query->leftJoin('subdistricts', 'subdistricts.id', '=', 'customers.subdistrict_id');
            $query->leftJoin('cities', 'cities.id', '=', 'subdistricts.city_id');
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('cities as send_city', 'send_city.id', '=', 'customers.send_city_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'customers.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'customers.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'customers.deleted_by');
            $query->select(
                'customers.*',
                'subdistricts.name as subdistrict_name',
                'cities.name as city_name',
                'provinces.name as province_name',
                'send_city.name as send_city_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getCustomerBankAccountGrid') {
            // Must be filtered by customer id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->customerGridService->getCustomerBankAccountGrid($filters['id'], $columnHead);

                    return $result;
                } else {
                    return response()->json(['success' => false, 'message' => 'Column head is required!'], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Filter by Customer Id is required!'], 400);
            }
        } elseif ($action == 'getItem') {
            $query->leftJoin('categories', 'categories.id', '=', 'items.category_id');
            $query->leftJoin('units', 'units.id', '=', 'items.unit_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'items.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'items.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'items.deleted_by');
            $query->select(
                'items.*',
                'categories.name as category_name',
                'units.name as unit_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } 

        // Transaction
        elseif ($action == 'getPurchase') {
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'purchases.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'purchases.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'purchases.deleted_by');
            $query->select(
                'purchases.*',
                'suppliers.name as supplier_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getPurchaseItemGrid') {
            // Must be filtered by purchase id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->purchaseGridService->getPurchaseItemGrid($filters['id'], $columnHead);

                    return $result;
                } else {
                    return response()->json(['success' => false, 'message' => 'Column head is required!'], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Filter by Purchase Id is required!'], 400);
            }
        }
        elseif ($action == 'getSale') {
            $query->leftJoin('customers', 'customers.id', '=', 'sales.customer_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'sales.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'sales.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'sales.deleted_by');
            $query->select(
                'sales.*',
                'customers.name as customer_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif ($action == 'getSaleItemGrid') {
            // Must be filtered by sale id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->saleGridService->getSaleItemGrid($filters['id'], $columnHead);

                    return $result;
                } else {
                    return response()->json(['success' => false, 'message' => 'Column head is required!'], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Filter by Sale Id is required!'], 400);
            }
        }
        elseif ($action == 'getOutgoingItemItemGrid' || $action == 'getIncomingItemItemGrid') {
            // Must be filtered by sale id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->stockTransferGridService->getStockTransferItemGrid($filters['id'], $columnHead);

                    return $result;
                } else {
                    return response()->json(['success' => false, 'message' => 'Column head is required!'], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Filter by Stock Transfer Id is required!'], 400);
            }
        }

        // REPORT
        elseif ($action == 'getStockReport') {
            $query->leftJoin('warehouses', 'warehouses.id', '=', 'stock_reports.warehouse_id');
            $query->leftJoin('items', 'items.id', '=', 'warehouses.item_id');
            $query->leftJoin('units', 'units.id', '=', 'items.unit_id');
            $query->select(
                'stock_reports.*',
                'warehouses.name as warehouse_name',
                'items.name as item_name',
                'units.name as unit_name',
            );
        }

        else {
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', $tableName . '.created_by')
                ->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', $tableName . '.updated_by')
                ->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', $tableName . '.deleted_by')
                ->select(
                    $tableName . '.*',
                    'created_by_user.name as created_by',
                    'updated_by_user.name as updated_by',
                    'deleted_by_user.name as deleted_by',
                );
        }

        $data = $query->get();

        $result = array('success' => true, 'data' => $data);
        return $result;
    }

    public function addData($request)
    {
        $actionsToModel = $this->globalActionController->getActionsToModel();

        $action = $request['action'];
        // get request body
        $requestBody = $request['requestData'];
        // get detail request payload for grid
        if (isset($requestBody['detail'])) {
            $detailPayload = $requestBody['detail'];
        } else {
            $detailPayload = '';
        }

        if (!array_key_exists($action, $actionsToModel)) {
            return response()->json(['success' => false, 'message' => 'Action not found'], 404);
        }

        // Menghapus atribut yang mengandung "_submit"
        foreach ($requestBody as $key => $value) {
            if (is_string($value) && Str::endsWith($key, '_submit')) {
                unset($requestBody[$key]);
            }
        }

        if ($action == 'addUser') {
            //encrypt password
            $password = bcrypt($requestBody['password']);
            $requestBody['branch_id'] = $detailPayload[0]['user_branch'][0]['branch_id'] ?? null;
            $requestBody['company_id'] = $detailPayload[3]['user_company'][0]['company_id'] ?? null;
            $requestBody['user_group_id'] = $detailPayload[2]['user_group'][0]['user_group_id'] ?? null;
        } else {
            if($action == 'addPurchase' || $action == 'addSale') {
                $requestBody['branch_code'] = Branch::where('id', $requestBody['branch_id'])->first();
                $stringBranch = ['code' => $requestBody['branch_code']['code']];
            } else {
                $stringBranch = [];
            }

            $formattedCode = $this->formatCode($requestBody['format_code'] ?? '', "", $stringBranch);
        }

        $requestBody['code'] = $formattedCode ?? $requestBody['manual_code'] ?? '';
        $requestBody['password'] = $password ?? '';
        $requestBody['created_by'] = auth()->id();

        try {
            $result = DB::transaction(function () use ($actionsToModel, $action, $requestBody, $detailPayload) {

                $data = $this->modelName($actionsToModel[$action])::create($requestBody);

                if ($action == 'addCustomer') {
                    if ($detailPayload != '') {
                        $customerBankAccounts = $detailPayload[0]['master_data_relation_customer'];

                        if ($customerBankAccounts != []) {
                            foreach ($customerBankAccounts as $i => $item) {
                                $customerBankAccount = [
                                    'bank_id' => $item['bank_id'] ?? null,
                                    'bank_account_number' => $item['bank_account_number'] ?? '-',
                                    'bank_account_name' => $item['bank_account_name'] ?? '-',
                                ];
                                $data->banks()->attach($customerBankAccount['bank_id'], $customerBankAccount);
                            }
                        }
                    }
                } 
                // Transaction
                elseif ($action == 'addPurchase') {
                    if ($detailPayload != '') {
                        $purchaseItems = $detailPayload[0]['transaction_purchase_item'];

                        if ($purchaseItems != []) {
                            foreach ($purchaseItems as $i => $item) {
                                $purchaseItem = [
                                    'item_id' => $item['item_id'] ?? null,
                                    'amount' => $item['amount'] ?? 0,
                                    'subtotal' => $item['subtotal'] ?? 0,
                                ];
                                $data->items()->attach($purchaseItem['item_id'], $purchaseItem);
                            }
                        }
                    }
                } elseif ($action == 'addSale') {
                    if ($detailPayload != '') {
                        $saleItems = $detailPayload[0]['transaction_sale_item'];

                        if ($saleItems != []) {
                            foreach ($saleItems as $i => $item) {
                                $saleItem = [
                                    'item_id' => $item['item_id'] ?? null,
                                    'amount' => $item['amount'] ?? 0,
                                    'subtotal' => $item['subtotal'] ?? 0,
                                ];
                                $data->items()->attach($saleItem['item_id'], $saleItem);
                            }
                        }
                    }
                } elseif ($action == 'addOutgoingItem') {
                    if ($detailPayload != '') {
                        $outgoingItems = $detailPayload[0]['transaction_warehouse_outgoing_item_item'];

                        if ($outgoingItems != []) {
                            foreach ($outgoingItems as $i => $item) {
                                $outgoingItem = [
                                    'item_id' => $item['item_id'] ?? null,
                                    'amount' => $item['amount'] ?? 0,
                                ];
                                $data->items()->attach($outgoingItem['item_id'], $outgoingItem);
                            }
                        }
                    }
                }

                return $data;
            });
            DB::commit();

            $result = array('success' => true, 'data' => $result);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            $result = array('success' => false, 'data' => $e);
            return $result;
        }
    }

    public function updateData($request, $id)
    {
        $actionsToModel = $this->globalActionController->getActionsToModel();

        // get action in request
        $action = $request['action'];
        // get request body
        $requestBody = $request['requestData'];

        // set user
        $requestBody['updated_by'] = auth()->id();

        // if action updateUser then remove confirm password key
        if ($action == 'updateUser') {
            if (array_key_exists('confirm_password', $requestBody)) {
                unset($requestBody['confirm_password']);
            }
        }

        // Menghapus atribut yang mengandung "_submit"
        foreach ($requestBody as $key => $value) {
            if (is_string($value) && Str::endsWith($key, '_submit')) {
                unset($requestBody[$key]);
            }
        }

        if (array_key_exists('_method', $requestBody) && array_key_exists('_token', $requestBody)) {
            unset($requestBody['_method']);
            unset($requestBody['_token']);
        }

        if (!array_key_exists($action, $actionsToModel)) {
            return response()->json(['success' => false, 'message' => 'Action not found'], 404);
        }

        $data = $this->modelName($actionsToModel[$action])::findOrFail($id);

        $result = DB::transaction(function () use ($actionsToModel, $action, $requestBody, $id) {

            if ($action == 'updateCustomer') {
                // update grid
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                if ($data) {
                    $data->update($requestBody);
                    $customerBankAccounts = [];

                    if (isset($requestBody['detail'])) {
                        $customerBankAccounts = $requestBody['detail'][0]['master_data_relation_customer'];
                    }

                    if (empty($customerBankAccounts)) {
                        $data->banks()->detach();
                    } else {
                        $data->banks()->detach();

                        if ($customerBankAccounts != []) {
                            foreach ($customerBankAccounts as $i => $item) {
                                $customerBankAccount = [
                                    'bank_id' => $item['bank_id'] ?? null,
                                    'bank_account_number' => $item['bank_account_number'] ?? '-',
                                    'bank_account_name' => $item['bank_account_name'] ?? '-',
                                ];
                                $data->banks()->attach($customerBankAccount['bank_id'], $customerBankAccount);
                            }
                        }
                    }
                }
            } 
            // Transaction
            elseif ($action == 'updatePurchase') {
                // update grid
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                if ($data) {
                    $data->update($requestBody);
                    $purchaseItems = [];

                    if (isset($requestBody['detail'])) {
                        $purchaseItems = $requestBody['detail'][0]['transaction_purchase_item'];
                    }

                    if (empty($purchaseItems)) {
                        $data->items()->detach();
                    } else {
                        $data->items()->detach();

                        if ($purchaseItems != []) {
                            foreach ($purchaseItems as $i => $item) {
                                $purchaseItem = [
                                    'item_id' => $item['item_id'] ?? null,
                                    'amount' => $item['amount'] ?? 0,
                                    'subtotal' => $item['subtotal'] ?? 0,
                                ];
                                $data->items()->attach($purchaseItem['item_id'], $purchaseItem);
                            }
                        }
                    }
                }
            } 
            // Approval
            elseif($action == 'updateStatusPurchase') {
                if (isset($requestBody['is_approve'])) {
                    $is_approve = true;
                    if ($requestBody['is_approve'] == 1) {
                        // Basic Approve
                        $newData = [
                            'approved_by' => auth()->id(),
                            'is_approve' => true,
                            'approved_at' => now(),
                        ];

                        // Find header purchase data
                        $purchase = Purchase::where('id', $id)->first();
                        // Find data item yang di purchase
                        $purchaseItem = PurchaseItemDetail::where('purchase_id', $id)->get();

                        if (!$purchaseItem->isEmpty()) {
                            foreach ($purchaseItem as $item) {
                                // Cari Warehouse yang sesuai dengan item tersebut
                                $warehouse = Warehouse::where('item_id', $item->item_id)->where('branch_id', $requestBody['branch_id'])->first();
                                // tambah stok tersedia
                                if($warehouse) {
                                    $warehouse->update([
                                        'stock' => $warehouse->stock + $item->amount,
                                        'stock_updated_at' => now()
                                    ]);
                                } else {
                                    $is_approve = false;
                                    $message = [
                                        'type' => 'Error',
                                        'message' => trans('warehouse_not_found')
                                    ];
                                    break;
                                }
                                // tambah data laporan barang masuk
                                StockReport::create([
                                    'transaction_code' => $purchase->code,
                                    'warehouse_id' => $warehouse->id,
                                    'amount' => '+' . $item->amount,
                                    'remark' => 'Approve Purchase',
                                    'date' => now()
                                ]);
                            }
                        } else {
                            $is_approve = false;
                            $message = [
                                'type' => 'Error',
                                'message' => trans('item_not_found')
                            ];
                        }

                        if ($is_approve) {
                            $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                                ->update($newData);
                        }
                    } else {
                        // Basic disapprove
                        $newData = [
                            'approved_by' => null,
                            'is_approve' => false,
                            'approved_at' => null,
                        ];

                        // Find header purchase data
                        $purchase = Purchase::where('id', $id)->first();
                        // Find data item yang di purchase
                        $purchaseItem = PurchaseItemDetail::where('purchase_id', $id)->get();

                        if (!$purchaseItem->isEmpty()) {
                            foreach ($purchaseItem as $item) {
                                // Cari Warehouse yang sesuai dengan item tersebut
                                $warehouse = Warehouse::where('item_id', $item->item_id)->where('branch_id', $requestBody['branch_id'])->first();
                                // tambah stok tersedia
                                if($warehouse) {
                                    $warehouse->update([
                                        'stock' => $warehouse->stock - $item->amount,
                                        'stock_updated_at' => now()
                                    ]);
                                }
                                // tambah data laporan barang masuk
                                StockReport::create([
                                    'transaction_code' => $purchase->code,
                                    'warehouse_id' => $warehouse->id,
                                    'amount' => '-' . $item->amount,
                                    'remark' => 'Disapprove Purchase',
                                    'date' => now()
                                ]);
                            }
                        }
                        
                        $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                            ->update($newData);
                    }
                    if (!$is_approve) {
                        return $message;
                    }
                }
            }
            // Transaction
            elseif ($action == 'updateSale') {
                // update grid
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                if ($data) {
                    $data->update($requestBody);
                    $saleItems = [];

                    if (isset($requestBody['detail'])) {
                        $saleItems = $requestBody['detail'][0]['transaction_sale_item'];
                    }

                    if (empty($saleItems)) {
                        $data->items()->detach();
                    } else {
                        $data->items()->detach();

                        if ($saleItems != []) {
                            foreach ($saleItems as $i => $item) {
                                $saleItem = [
                                    'item_id' => $item['item_id'] ?? null,
                                    'amount' => $item['amount'] ?? 0,
                                    'subtotal' => $item['subtotal'] ?? 0,
                                ];
                                $data->items()->attach($saleItem['item_id'], $saleItem);
                            }
                        }
                    }
                }
            } 
            // Approval
            elseif($action == 'updateStatusSale') {
                if (isset($requestBody['is_approve'])) {
                    if ($requestBody['is_approve'] == 1) {
                        $newData = [
                            'approved_by' => auth()->id(),
                            'is_approve' => true,
                            'approved_at' => now(),
                        ];
                    } else {
                        $newData = [
                            'approved_by' => null,
                            'is_approve' => false,
                            'approved_at' => null,
                        ];
                    }
                }
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                    ->update($newData);
            }
            elseif ($action == 'updateOutgoingItem') {
                // update grid
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                if ($data) {
                    $data->update($requestBody);
                    $outgoingItems = [];

                    if (isset($requestBody['detail'])) {
                        $outgoingItems = $requestBody['detail'][0]['transaction_warehouse_outgoing_item_item'];
                    }

                    if (empty($outgoingItems)) {
                        $data->items()->detach();
                    } else {
                        $data->items()->detach();

                        if ($outgoingItems != []) {
                            foreach ($outgoingItems as $i => $item) {
                                $outgoingItem = [
                                    'item_id' => $item['item_id'] ?? null,
                                    'amount' => $item['amount'] ?? 0,
                                ];
                                $data->items()->attach($outgoingItem['item_id'], $outgoingItem);
                            }
                        }
                    }
                }
            } 
            // Approval
            elseif($action == 'updateStatusOutgoingItem') {
                if (isset($requestBody['is_approve_1'])) {
                    $is_approve = true;
                    if ($requestBody['is_approve_1'] == 1) {
                        $newData = [
                            'approved_1_by' => auth()->id(),
                            'is_approve_1' => true,
                            'approved_1_at' => now(),
                        ];

                        // Find header stockTransfer data
                        $stockTransfer = StockTransfer::where('id', $id)->first();
                        // Find data item yang di stockTransfer
                        $stockTransferItem = StockTransferItemDetail::where('stock_transfer_id', $id)->get();

                        if (!$stockTransferItem->isEmpty()) {
                            foreach ($stockTransferItem as $item) {
                                // Cari Warehouse yang sesuai dengan item tersebut
                                $warehouse = Warehouse::where('item_id', $item->item_id)->where('branch_id', $requestBody['branch_id'])->first();
                                // kurangi stok tersedia
                                if($warehouse) {
                                    $warehouse->update([
                                        'stock' => $warehouse->stock - $item->amount,
                                        'stock_updated_at' => now()
                                    ]);
                                } else {
                                    $is_approve = false;
                                    $message = [
                                        'type' => 'Error',
                                        'message' => trans('warehouse_not_found')
                                    ];
                                    break;
                                }
                                // tambah data laporan barang masuk
                                StockReport::create([
                                    'transaction_code' => $stockTransfer->code,
                                    'warehouse_id' => $warehouse->id,
                                    'amount' => '-' . $item->amount,
                                    'remark' => 'Approve Stock Transfer (Outgoing)',
                                    'date' => now()
                                ]);
                            }
                        } else {
                            $is_approve = false;
                            $message = [
                                'type' => 'Error',
                                'message' => trans('item_not_found')
                            ];
                        }

                        if ($is_approve) {
                            $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                                ->update($newData);
                        }
                    } else {
                        $newData = [
                            'approved_1_by' => null,
                            'is_approve_1' => false,
                            'approved_1_at' => null,
                        ];

                        // Find header stockTransfer data
                        $stockTransfer = StockTransfer::where('id', $id)->first();
                        $stockTransferCode = $stockTransfer->code;

                        // Find data item yang di purchase
                        $stockTransfer = StockTransferItemDetail::where('stock_transfer_id', $id)->get();

                        if (!$stockTransfer->isEmpty()) {
                            foreach ($stockTransfer as $item) {
                                // Cari Warehouse yang sesuai dengan item tersebut
                                $warehouse = Warehouse::where('item_id', $item->item_id)->where('branch_id', $requestBody['branch_id'])->first();
                                // tambah stok tersedia
                                if($warehouse) {
                                    $warehouse->update([
                                        'stock' => $warehouse->stock + $item->amount,
                                        'stock_updated_at' => now()
                                    ]);
                                }
                                // tambah data laporan barang masuk
                                StockReport::create([
                                    'transaction_code' => $stockTransferCode,
                                    'warehouse_id' => $warehouse->id,
                                    'amount' => '+' . $item->amount,
                                    'remark' => 'Disapprove Stock Transfer (Outgoing)',
                                    'date' => now()
                                ]);
                            }
                        }
                        
                        $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                            ->update($newData);
                    }
                    if (!$is_approve) {
                        return $message;
                    }
                }
            }
            // Approval
            elseif($action == 'updateStatusIncomingItem') {
                if (isset($requestBody['is_approve_2'])) {
                    $is_approve = true;
                    if ($requestBody['is_approve_2'] == 1) {
                        $newData = [
                            'approved_2_by' => auth()->id(),
                            'is_approve_2' => true,
                            'approved_2_at' => now(),
                        ];

                        // Find header stockTransfer data
                        $stockTransfer = StockTransfer::where('id', $id)->first();
                        // Find data item yang di stockTransfer
                        $stockTransferItem = StockTransferItemDetail::where('stock_transfer_id', $id)->get();

                        if (!$stockTransferItem->isEmpty()) {
                            foreach ($stockTransferItem as $item) {
                                // Cari Warehouse yang sesuai dengan item tersebut
                                $warehouse = Warehouse::where('item_id', $item->item_id)->where('branch_id', $requestBody['branch_id'])->first();
                                // kurangi stok tersedia
                                if($warehouse) {
                                    $warehouse->update([
                                        'stock' => $warehouse->stock + $item->amount,
                                        'stock_updated_at' => now()
                                    ]);
                                } else {
                                    $is_approve = false;
                                    $message = [
                                        'type' => 'Error',
                                        'message' => trans('warehouse_not_found')
                                    ];
                                    break;
                                }
                                // tambah data laporan barang masuk
                                StockReport::create([
                                    'transaction_code' => $stockTransfer->code,
                                    'warehouse_id' => $warehouse->id,
                                    'amount' => '+' . $item->amount,
                                    'remark' => 'Approve Stock Transfer (Incoming)',
                                    'date' => now()
                                ]);
                            }
                        } else {
                            $is_approve = false;
                            $message = [
                                'type' => 'Error',
                                'message' => trans('item_not_found')
                            ];
                        }

                        if ($is_approve) {
                            $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                                ->update($newData);
                        }
                    } else {
                        $newData = [
                            'approved_2_by' => null,
                            'is_approve_2' => false,
                            'approved_2_at' => null,
                        ];

                        // Find header stockTransfer data
                        $stockTransfer = StockTransfer::where('id', $id)->first();
                        $stockTransferCode = $stockTransfer->code;

                        // Find data item yang di purchase
                        $stockTransfer = StockTransferItemDetail::where('stock_transfer_id', $id)->get();

                        if (!$stockTransfer->isEmpty()) {
                            foreach ($stockTransfer as $item) {
                                // Cari Warehouse yang sesuai dengan item tersebut
                                $warehouse = Warehouse::where('item_id', $item->item_id)->where('branch_id', $requestBody['branch_id'])->first();
                                // tambah stok tersedia
                                if($warehouse) {
                                    $warehouse->update([
                                        'stock' => $warehouse->stock - $item->amount,
                                        'stock_updated_at' => now()
                                    ]);
                                }
                                // tambah data laporan barang masuk
                                StockReport::create([
                                    'transaction_code' => $stockTransferCode,
                                    'warehouse_id' => $warehouse->id,
                                    'amount' => '-' . $item->amount,
                                    'remark' => 'Disapprove Stock Transfer (Incoming)',
                                    'date' => now()
                                ]);
                            }
                        }
                        
                        $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                            ->update($newData);
                    }
                    if (!$is_approve) {
                        return $message;
                    }
                }
            }
            
            // Default action
            else {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update($requestBody);
            }

            return $data;
        });

        if (isset($result['type'])) {
            if ($result['type'] === 'Error') {
                $result = array('success' => false, 'message' => $result['message']);
                return $result;
            }
        }

        $data = $this->modelName($actionsToModel[$action])::where('id', $id)->get();
        $result = array('success' => true, 'data' => $data);

        return $result;
    }

    public function softDeleteData($request, $id)
    {
        $actionsToModel = $this->globalActionController->getActionsToModel();
        // get action in request
        $action = $request['action'];

        if (!array_key_exists($action, $actionsToModel)) {
            return response()->json(['success' => false, 'message' => 'Action not found'], 404);
        }

        $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update(['deleted_by' => auth()->id()]);
        $data = $this->modelName($actionsToModel[$action])::where('id', $id)->delete();

        $result = array('success' => true, 'data' => $data);

        return $result;
    }

    public function switchLang($lang, Request $request)
    {
        $getUrlPrev = URL::previous();

        if (array_key_exists($lang, Config::get('languages'))) {
            app()->setLocale($lang);
            Session::put('applocale', $lang);
        }

        return redirect($getUrlPrev);
    }

    public function getAjaxDataTable(Request $request, $action)
    {
        $select = ['id'];
        if (isset($request->columns)) {
            foreach ($request->columns as $data) {
                if ($data['name'] !== 'action' && $data['name'] !== null)
                    $select[] = $data['name'];
            }
        }

        if(isset($request->filters)) {
            $search_key[] = array(
                'key' => $request->filters['key'],
                'term' => $request->filters['term'],
                'query' => $request->filters['query']
            );
    
            $set_request = SetRequestGlobal($action, search: $search_key);
        } else {
            $set_request = SetRequestGlobal($action);
        }

        $data = $this->getData($set_request);

        if (isset($data['success'])) {
            if ($data['success'] === true) {
                // Modify each row's data to include buttons
                foreach ($data['data'] as $row) {
                    if (Route::has($request->route . '.edit')) {
                        $row->editUrl = route($request->route . '.edit', $row->id);
                    }
                    if (Route::has($request->route . '.destroy')) {
                        $row->destroyUrl = route($request->route . '.destroy', $row->id);
                    }
                    if (Route::has($request->route . '.show')) {
                        $row->showUrl = route($request->route . '.show', $row->id);
                    }
                    if (isset($row->is_approve) || isset($row->is_approve_1) || isset($row->is_approve_2)) {
                        $row->approveUrl = route($request->route . '.show', $row->id . ',approve');
                    }
                    if (isset($row->is_approve) || isset($row->is_approve_1) || isset($row->is_approve_2)) {
                        $row->disapproveUrl = route($request->route . '.show', $row->id . ',disapprove');
                    }
                    if (isset($row->is_approve)) {
                        $row->printUrl = route($request->route . '.show', $row->id . ',print');
                    }

                    // change is active status
                    if ($row->is_active || $row->is_active === 1) {
                        $row->is_active = 'Active';
                    } elseif (!$row->is_active || $row->is_active === 0) {
                        $row->is_active = 'Inactive';
                    }

                    // change is approve status
                    if ($row->is_approve || $row->is_approve === 1) {
                        $row->is_approve = 'Approved';
                    } elseif (!$row->is_approve || $row->is_approve === 0) {
                        $row->is_approve = 'Not Approved';
                    }

                    if ($row->is_approve_1 || $row->is_approve_1 === 1) {
                        $row->is_approve_1 = 'Approved';
                    } elseif (!$row->is_approve_1 || $row->is_approve_1 === 0) {
                        $row->is_approve_1 = 'Not Approved';
                    }

                    if ($row->is_approve_2 || $row->is_approve_2 === 1) {
                        $row->is_approve_2 = 'Approved';
                    } elseif (!$row->is_approve_2 || $row->is_approve_2 === 0) {
                        $row->is_approve_2 = 'Not Approved';
                    }

                    // change is stock updated status
                    if ($row->stock_updated_at && $row->stock_updated_at === null) {
                        $row->stock_updated_at = 'Never';
                    }
                }

                return response()->json($data);
            }
        } else {
            return response()->json(['message' => $data], 404);
        }
    }

    public function getBrowseData(Request $request)
    {
        $set_table = renderTableGlobalAjax($request->get('head_table'), $request->get('id_ajax'));

        $header_content = renderModelHeaderForm($request->get('table_name'));
        $filter = [];
        $input_param = [];
        if ($request->get('filter')) {
            $filter = json_decode($request->get('filter'), true);
        }
        if ($request->get('input_param')) {
            $input_param = json_decode($request->get('filter'), true);
        }

        $initTableModal = initializeDataTableModal($request->get('action'), $request->get('field_table'), $request->get('output_param'), $filter, $input_param, $request->get('id_ajax'));
        $response = [
            'header' => $header_content,
            'body_content' => $set_table,
            'footer' => '',
            'init_table_modal' => $initTableModal,
        ];

        return json_encode($response);
    }

    public function autoComplete(Request $request)
    {
        $query = '';
        $input_param = '';
        $filter = [];
        $get_data = '';
        $search_key = [];
        $custom_filter = [];

        if ($request->get('search') != null) {
            $query = $request->get('search');
        }
        if ($request->get('input_param') != null) {
            $input_param = $request->get('input_param');
        }
        if ($request->get('get_data') != '') {
            $get_data = $request->get('get_data');
        }

        $arrayFilter = array('skip' => 0, 'take' => 50);

        if (!empty($request->get('search_term')) || $request->get('search_term') != null) {
            foreach ($request->get('search_term') as $value) {
                $param = explode("|", $value);
                if (count($param) > 2) {
                    $search_key[] = array(
                        'key' => $param[0],
                        'term' => $param[1],
                        'query' => $param[2]
                    );
                } else {
                    $search_key[] = array(
                        'key' => $param[0],
                        'term' => $param[1],
                        'query' => $query
                    );
                }
            }
        }

        $set_request = SetRequestGlobal(action: $request->get('action'), search: $search_key, filter: $arrayFilter, get_data: $get_data);

        if (!empty($request->get('filter')) || $request->get('filter') != null) {
            $filter = json_decode($request->get('filter'), true);
            // custom filter

            if (!empty($filter)) {
                foreach ($filter as $key => $value) {
                    $val = $value;
                    $custom_filter[] = array(
                        'key' => $key,
                        'term' => 'equal',
                        'query' => $val
                    );
                }
            }
        }

        if (!empty($input_param)) {
            $set_input_param = json_decode($input_param, true);

            // custom filter
            if (!empty($set_input_param)) {

                foreach ($set_input_param as $key => $value) {
                    $set_value = $value['query'];
                    if ($value['query'] == 'on') {
                        $set_value = 'true';
                    }
                    $custom_filter[] = array(
                        'key' => $value['key'],
                        'term' => $value['term'],
                        'query' => $set_value,
                    );
                }
            }
        }

        if (!empty($custom_filter)) {
            $set_request = SetRequestGlobal(action: $request->get('action'), search: $search_key, filter: $arrayFilter, get_data: $get_data, custom_filters: $custom_filter);
        }
        $result = $this->getData($set_request);

        if ($result['success'] == false) {
            return json_encode($result);
        }

        $temp_response = [];
        $response = [];

        if ($request->get('is_grid') != null) {
            foreach ($result['data'] as $data) {
                $temp_label = '';
                $show_value = $request->get('show_value');

                for ($i = 0; $i < count($show_value); $i++) {
                    if ($i > 0) {
                        $temp_label .= " - " . $data[$show_value[$i]];
                    } else {
                        $temp_label .= $data[$show_value[$i]];
                    }
                }

                $temp_result = '';
                $result_show = $request->get('result_show');

                for ($i = 0; $i < count($result_show); $i++) {
                    if ($i > 0) {
                        $temp_result .= " - " . $data[$result_show[$i]];
                    } else {
                        $temp_result .= $data[$result_show[$i]];
                    }
                }
                $data['visible'] = $temp_label;
                $data['result'] = $temp_result;
                array_push($temp_response, $data);
            }
            $response = [
                'items' => $temp_response
            ];
        } else {
            foreach ($result['data'] as $data) {
                $temp_label = '';
                $decode = json_decode($request->get('show_value'));

                for ($i = 0; $i < count($decode); $i++) {
                    if ($i > 0) {
                        $temp_label .= " - " . $data[$decode[$i]];
                    } else {
                        $temp_label .= $data[$decode[$i]];
                    }
                }
                $data['label'] = $temp_label;

                $response[] = array("value" => $data['id'], "label" => $data['label'], "data" => $data);
            }
        }

        return json_encode($response);
    }

    public function showWarning()
    {
        $message = session('message');

        $objResponse = [
            'title' => $this->globalVariable->module,
            'subtitle' =>  $this->globalVariable->subModule,
            'menu' => $this->globalVariable->menuUrl,
            'mode' => 'index'
        ];

        return view('warning', $objResponse)->with('message', $message); // Load the 'warning.blade.php' view
    }

    public function callRenderBody(Request $request)
    {
        try {
            $get_type = $request->get('type');
            $param = '';
            $text = '';
            if ($request->get('render') != null) {
                $get_render = json_decode($request->input('render'), true);
            }
            if ($request->get('class_icon') != null) {
                $class_icon = $request->get('class_icon');
            }
            $div = 'no';
            if ($request->get('div') != null) {
                $div = $request->get('div');
            }
            if ($request->get('color') != null) {
                $color = $request->get('color');
            }
            if ($request->get('title') != null) {
                $title = $request->get('title');
            }
            if ($request->get('text') != null) {
                $text = $request->get('text');
            }
            if ($request->get('id') != null) {
                $id = $request->get('id');
            }
            if ($request->get('route') != null) {
                $route = $request->get('route');
            }
            if ($request->get('param') != null) {
                $param = $request->get('param');
            }
            $category = '';
            if ($request->get('category') != null) {
                $category = $request->get('category');
            }
            if ($request->get('text_button_cancel') != null) {
                $text_button_cancel = $request->get('text_button_cancel');
            }
            if ($request->get('text_button_ok') != null) {
                $text_button_ok = $request->get('text_button_ok');
            }
            $method = "delete";
            if ($request->get('method') != null) {
                $method = $request->get('method');
            }
            $name = "id";
            if ($request->get('name') != null) {
                $name = $request->get('name');
            }
            $is_footer = '';
            if ($request->get('is_footer') != '') {
                $is_footer = $request->get('is_footer');
            }

            $header_content = "";
            $body_content = "";
            $footer = "";

            // Your helper function logic here
            if ($get_type == 'alert') {
                $body_content = renderModelBodyAlert($class_icon, $color, $title, $text);
                $footer = renderModelFooter($text_button_cancel, $text_button_ok, "btn btn-dark", "btn btn-primary");
            } elseif ($get_type == 'success') {
                $body_content = renderModelBodyConfirmation($class_icon, $color, $title, $text);
                $footer = renderModelFooter('', 'Oke', "", "btn btn-primary");
            } elseif ($get_type == 'confirmations') {
                $body_content = renderModelBodyConfirmation($class_icon, $color, $title, $text);
                $footer = renderModelFooterMethodConfirmation('button_cancel', 'button_confirm', "btn btn-dark", "btn btn-primary", $id, $route, $param, $method, $name);
            } else {
                $header_content = renderModelHeaderForm($title);
                $renderJSON = json_encode($get_render);
                $renderArray = json_decode($renderJSON, true);
                if ($category == '') {
                    $generateRoute = route($route);
                } else {
                    $generateRoute = route($route . '.' . $category, []);
                }

                $body_content = renderBodyModalForm($generateRoute, $method, '', $renderArray, $div, $text, '', '', '', '');
            }

            $response = [
                'header_content' => $header_content,
                'body_content' => $body_content,
                'footer' => $footer,
            ];

            return json_encode($response);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()], 500);
        }
    }
}
