<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GlobalVariable;
use App\Http\Services\CustomerGridService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GlobalController extends Controller
{
    private $globalVariable;

    public function __construct(
        GlobalActionController $globalActionController,
        CustomerGridService $customerGridService,
    ) {
        $this->globalActionController = $globalActionController;
        $this->customerGridService = $customerGridService;
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
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'warehouses.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'warehouses.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'warehouses.deleted_by');
            $query->select(
                'warehouses.*',
                'branches.name as branch_name',
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
            $query->leftJoin('types', 'types.id', '=', 'items.type_id');
            $query->leftJoin('categories', 'categories.id', '=', 'items.category_id');
            $query->leftJoin('units', 'units.id', '=', 'items.unit_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'items.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'items.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'items.deleted_by');
            $query->select(
                'items.*',
                'categories.name as category_name',
                'types.name as type_name',
                'units.name as unit_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else {
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
            // generate code
            $formattedCode = $this->formatCode($requestBody['format_code'] ?? '', "", []);
        }

        // code: formattedCode | manualCode
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
                // if ($action == 'addUser') {
                //     $lastInsertedId = $data->id;
                //     // insert detail payload 'user_branch' into user_branches
                //     if (!empty($detailPayload[0]['user_branch'])) {
                //         $branchIds = [];

                //         foreach ($detailPayload[0]['user_branch'] as $item) {
                //             if (isset($item['branch_id'])) {
                //                 $branchIds[] = $item['branch_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['branch_id'],
                //                 'relation_type' => 'master_branch',
                //                 'relation_table' => 'branches',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }

                //         $data->branches()->attach($branchIds);
                //     }

                //     if (!empty($detailPayload[1]['user_branch_report'])) {
                //         $branchReportIds = [];

                //         foreach ($detailPayload[1]['user_branch_report'] as $item) {

                //             if (isset($item['branch_id'])) {
                //                 $branchReportIds[] = $item['branch_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['branch_id'],
                //                 'relation_type' => 'master_branch_report',
                //                 'relation_table' => 'branches',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }
                //     }

                //     if (!empty($detailPayload[2]['user_group'])) {
                //         $userGroupIds = [];

                //         foreach ($detailPayload[2]['user_group'] as $item) {
                //             if (isset($item['user_group_id'])) {
                //                 $userGroupIds[] = $item['user_group_id'];
                //             }
                //         }

                //         $data->user_groups()->attach($userGroupIds);
                //     }

                //     if (!empty($detailPayload[3]['user_company'])) {
                //         $companyIds = [];

                //         foreach ($detailPayload[3]['user_company'] as $item) {
                //             if (isset($item['company_id'])) {
                //                 $companyIds[] = $item['company_id'];
                //             }
                //         }

                //         $data->companies()->attach($companyIds);
                //     }

                //     if (!empty($detailPayload[4]['access_application'])) {

                //         $accessApplicationIds = [];

                //         foreach ($detailPayload[4]['access_application'] as $item) {
                //             if (isset($item['webstite_access_id'])) {
                //                 $accessApplicationIds[] = $item['webstite_access_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['webstite_access_id'],
                //                 'relation_type' => 'master_website',
                //                 'relation_table' => 'websites',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }
                //     }

                //     if (!empty($detailPayload[5]['access_file_principal'])) {
                //         $accessPrincipalFileIds = [];

                //         foreach ($detailPayload[5]['access_file_principal'] as $item) {
                //             if (isset($item['principal_file_access_id'])) {
                //                 $accessPrincipalFileIds[] = $item['principal_file_access_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['principal_file_access_id'],
                //                 'relation_type' => 'master_principal_file',
                //                 'relation_table' => 'principals',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }
                //     }

                //     if (!empty($detailPayload[6]['access_file_principal_group'])) {
                //         $accessPrincipalGroupFileIds = [];

                //         foreach ($detailPayload[6]['access_file_principal_group'] as $item) {
                //             if (isset($item['principal_group_access_file_id'])) {
                //                 $accessPrincipalGroupFileIds[] = $item['principal_group_access_file_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['principal_group_access_file_id'],
                //                 'relation_type' => 'master_principal_group_file',
                //                 'relation_table' => 'principal_groups',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }
                //     }

                //     if (!empty($detailPayload[7]['access_webbooking_principal_group'])) {
                //         $accessPrincipalGroupIds = [];

                //         foreach ($detailPayload[7]['access_webbooking_principal_group'] as $item) {
                //             if (isset($item['principal_group_web_access_id'])) {
                //                 $accessPrincipalGroupIds[] = $item['principal_group_web_access_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['principal_group_web_access_id'],
                //                 'relation_type' => 'master_principal_group',
                //                 'relation_table' => 'principal_groups',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }
                //     }

                //     if (!empty($detailPayload[8]['access_doc_dist'])) {
                //         $accessDocumentDistributionIds = [];

                //         foreach ($detailPayload[8]['access_doc_dist'] as $item) {
                //             if (isset($item['doc_access_id'])) {
                //                 $accessDocumentDistributionIds[] = $item['doc_access_id'];
                //             }

                //             AccessMaster::create([
                //                 'user_id' => $lastInsertedId,
                //                 'relation_id' => $item['doc_access_id'],
                //                 'relation_type' => 'master_document_distribution',
                //                 'relation_table' => 'document_distributions',
                //                 'remark' => $item['remark'],
                //             ]);
                //         }
                //     }
                // } else if ($action == 'addUserTeam') {
                //     // dd($detailPayload);
                //     if ($detailPayload != '' && $detailPayload != []) {
                //         foreach ($detailPayload[0]['users'] as $item) {
                //             if (isset($requestBody['is_lnj2'])) {
                //                 $details[] = [
                //                     'user_id' => $item['user_id'],
                //                     'remark' => $item['remark'] ?? '-',
                //                     'ref_id' => $item['ref_id']
                //                 ];
                //             } else {
                //                 $details[] = [
                //                     'user_id' => $item['user_id'],
                //                     'remark' => $item['remark'] ?? '-'
                //                 ];
                //             }
                //         }
                //         $createdDetails = $data->userTeamDetails()->createMany($details);
                //         // Collect IDs of the inserted records
                //         $UserTeamDetailIds = $createdDetails->pluck('id')->toArray();
                //     }
                // } else if ($action == 'addPrincipal') {
                //     try {
                //         try {
                //             $principalBlacklistPayload = [
                //                 'is_active' => true,
                //             ];

                //             $data->principalBlacklists()->create($principalBlacklistPayload);
                //         } catch (\Exception $e) {
                //             $timestamp = date('Y-m-d H:i:s');
                //             $errorMessage = $e->getMessage();
                //             $logEntry = "$timestamp - $errorMessage\n";
                //             File::append(storage_path('logs/error.log'), $logEntry);
                //         }

                //         if (isset($requestBody["bank_no"]) && isset($requestBody["bank_name"])) {
                //             if ($requestBody["bank_no"] != "" && $requestBody["bank_name"] != null) {
                //                 $bankAccountPayload = [
                //                     'number' => $requestBody["bank_no"],
                //                     'name' => $requestBody["bank_name"],
                //                 ];
                //                 $data->bankAccounts()->create($bankAccountPayload);
                //             }
                //         }


                //         if ($detailPayload != '' && $detailPayload != []) {
                //             $detailCommodities = [];

                //             $detailPayloadCommodities = $detailPayload[0]['principal_commodity'];
                //             $formattedCode = FormattingCodeHelper::formatCode('principal_commodity_category', "", [], [], [], null);


                //             if ($detailPayloadCommodities != []) {
                //                 foreach ($detailPayloadCommodities as $i => $item) {

                //                     $detailCommodities[] = [
                //                         'name' => $item['name'] ?? '-',
                //                         'imo' => $item['imo'] ?? '-',
                //                         'un' => $item['un'] ?? '-',
                //                         'pck_grp' => $item['pck_grp'] ?? '-',
                //                         'fi_pt' => $item['fi_pt'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                         'code' =>  $formattedCode,
                //                     ];
                //                 }

                //                 $createdPrincipalCommodities = $data->principalCommodities()->createMany($detailCommodities);
                //                 $principalCommoditiesDetailIds = $createdPrincipalCommodities->pluck('id')->toArray();
                //             }

                //             if ($detailPayload[1]['principal_pic'] != []) {
                //                 foreach ($detailPayload[1]['principal_pic'] as $item) {
                //                     $detailPics[] = [
                //                         'name' => $item['name'] ?? '-',
                //                         'phone' => $item['phone'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                     ];
                //                 }
                //                 $createdPrincipalPics = $data->principalPics()->createMany($detailPics);
                //                 $principalPicsIds = $createdPrincipalPics->pluck('id')->toArray();
                //             }

                //             if ($detailPayload[2]['principal_category'] != []) {
                //                 foreach ($detailPayload[2]['principal_category'] as $item) {
                //                     $detailCategories[] = [
                //                         'principal_category_id' => $item['principal_category_id'],
                //                         'remark' => $item['remark'] ?? '-',
                //                     ];
                //                 }
                //                 $createdPrincipalCategories = $data->principalCategoryDetails()->createMany($detailCategories);
                //                 $principalCategoriesIds = $createdPrincipalCategories->pluck('id')->toArray();
                //             }


                //             // //TODO add service from supplier [3]

                //             if ($detailPayload[4]['address'] != []) {
                //                 foreach ($detailPayload[4]['address'] as $item) {
                //                     $detailAddresses[] = [
                //                         'address' => $item['address']  ?? '-',
                //                         'pic' => $item['pic']  ?? '-',
                //                         'phone' => $item['phone']  ?? '-',
                //                         'email' => $item['email']  ?? '-',
                //                         'contact' => $item['contact'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                         'note' => $item['note'] ?? '-',
                //                         'is_visible' => $item['is_visible'],
                //                         'district_id' => $item['district_id'],
                //                     ];
                //                 }
                //                 $createdPrincipalAddresses = $data->principalAddresses()->createMany($detailAddresses);
                //                 $principalAddressesIds = $createdPrincipalAddresses->pluck('id')->toArray();
                //             }
                //         }
                //     } catch (\Exception $e) {
                //         $timestamp = date('Y-m-d H:i:s');
                //         $errorMessage = $e->getMessage();
                //         $logEntry = "$timestamp - $errorMessage\n";
                //         File::append(storage_path('logs/error.log'), $logEntry);
                //     }
                // } else if ($action == 'addQuotation') {
                //     if ($detailPayload != '' && $detailPayload != []) {
                //         if ($detailPayload[0]['cargo_details']) {
                //             if (isset($requestBody['is_lnj2'])) {
                //                 foreach ($detailPayload[0]['cargo_details'] as $item) {
                //                     $detailCargo[] = [
                //                         'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                //                         'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                //                         'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                //                         'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                //                         'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                //                         'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                //                         'quantity' =>  isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                //                         'total' => $item['total'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                         'container_type_id' => $item['container_type_id'] ?? null,
                //                         'container_size_id' => $item['container_size_id'] ?? null,
                //                         'ref_id' => $item['ref_id']
                //                     ];
                //                 }
                //             } else {
                //                 foreach ($detailPayload[0]['cargo_details'] as $item) {
                //                     $detailCargo[] = [
                //                         'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                //                         'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                //                         'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                //                         'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                //                         'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                //                         'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                //                         'quantity' =>  isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                //                         'total' => $item['total'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                         'container_type_id' => $item['container_type_id'] ?? null,
                //                         'container_size_id' => $item['container_size_id'] ?? null
                //                     ];
                //                 }
                //             }

                //             $createdCargoDetails = $data->rfqCargoes()->createMany($detailCargo);
                //             // Collect IDs of the inserted records
                //             $rfqCargoDetailIds = $createdCargoDetails->pluck('id')->toArray();
                //         }

                //         if ($detailPayload[1]['service_buy']) {
                //             if (isset($requestBody['is_lnj2'])) {
                //                 foreach ($detailPayload[1]['service_buy'] as $item) {
                //                     $detailBuy[] = [
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                //                         'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                //                         'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'BUY',
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'principal_id' => $item['principal_id'] ?? null,
                //                         'service_price_id' => $item['service_price_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'costing_currency_id' => $item['costing_currency_id'] ?? null,
                //                         'service_id' => $item['service_id'] ?? null,
                //                         'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                //                         'ref_id' => $item['ref_id']
                //                     ];
                //                 }
                //             } else {
                //                 foreach ($detailPayload[1]['service_buy'] as $item) {
                //                     $detailBuy[] = [
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                //                         'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                //                         'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'BUY',
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'service_price_id' => $item['service_price_id'] ?? null,
                //                         'principal_id' => $item['principal_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'service_id' => $item['service_id'] ?? null,
                //                         'costing_currency_id' => $item['costing_currency_id'] ?? null,
                //                         'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                //                     ];
                //                 }
                //             }
                //             $createdServiceBuyDetails = $data->rfqDetails()->createMany($detailBuy);
                //             // Collect IDs of the inserted records
                //             $rfqServiceBuyDetailIds = $createdServiceBuyDetails->pluck('id')->toArray();
                //         }

                //         if ($detailPayload[2]['service_sell']) {
                //             if (isset($requestBody['is_lnj2'])) {
                //                 foreach ($detailPayload[2]['service_sell'] as $item) {
                //                     $detailSell[] = [
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                //                         'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'service_price_id' => $item['service_price_id'] ?? null,
                //                         'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'SELL',
                //                         'service_id' => $item['service_id'] ?? null,
                //                         'principal_id' => $item['principal_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                //                         'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                //                         'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                //                         'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                //                         'ref_id' => $item['ref_id']
                //                     ];
                //                 }
                //             } else {
                //                 foreach ($detailPayload[2]['service_sell'] as $item) {
                //                     $detailSell[] = [
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'service_price_id' => $item['service_price_id'] ?? null,
                //                         'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                //                         'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'SELL',
                //                         'service_id' => $item['service_id'] ?? null,
                //                         'principal_id' => $item['principal_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                //                         'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                //                         'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                //                         'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                //                     ];
                //                 }
                //             }

                //             $createdServiceSellDetails = $data->rfqDetails()->createMany($detailSell);
                //             // Collect IDs of the inserted records
                //             $rfqServiceSellDetailIds = $createdServiceSellDetails->pluck('id')->toArray();
                //         }
                //     }
                // } else if ($action == 'addPreOrder') {
                //     if ($detailPayload != '') {

                //         $detailParties = [];
                //         $detailPackings = [];
                //         $detailItemShipment = [];
                //         $detailContainer = [];

                //         foreach ($detailPayload as $value) {

                //             if (array_key_exists('detail_party', $value)) {

                //                 if (!empty($value)) {
                //                     foreach ($detailPayload[0]['detail_party'] as $item) {
                //                         $detailParties[] = [
                //                             'container_type_id' => $item['container_type_id'] ?? 0,
                //                             'container_size_id' => $item['container_size_id'] ?? 0,
                //                             'qty' => $item['qty'] ?? 4,
                //                             'remark' => $item['remark'] ?? '-'
                //                         ];
                //                     }
                //                     $data->preOrderParty()->createMany($detailParties);
                //                 }
                //             } elseif (array_key_exists('detail_packing', $value)) {
                //                 if (!empty($value)) {
                //                     foreach ($detailPayload[1]['detail_packing'] as $item) {
                //                         $detailPackings[] = [
                //                             'packing_detail_id' => $item['packing_detail_id'] ?? null,
                //                             'container_size_id' => $item['container_size_id'] ?? null,
                //                             'package_type' => $item['package_type'] ?? 'C',
                //                             'packing_no' => $item['packing_no'] ?? '',
                //                             'packing_size' => $item['packing_size'] ?? '',
                //                             'packing_type' => $item['packing_type'] ?? '',
                //                             'uom' => $item['uom'] ?? '',
                //                             'qty' => $item['qty'] ?? null,
                //                             'seal_no' => $item['seal_no'] ?? '',
                //                             'etd_factory' => $item['etd_factory'] ?? now(),
                //                             'parent_id' => $item['parent_id'] ?? null,
                //                             'shipment_item_id' => $item['shipment_item_id'] ?? null,
                //                             'is_in_container' => $item['is_in_container'] ?? false,
                //                             'remark' => $item['remark'] ?? '-'
                //                         ];
                //                     }
                //                     $data->posPackingDetail()->createMany($detailPackings);
                //                 }
                //             } elseif (array_key_exists('detail_item_shipment', $value)) {
                //                 if (!empty($value)) {
                //                     foreach ($detailPayload[2]['detail_item_shipment'] as $item) {
                //                         $detailItemShipment[] = [
                //                             'item_shipment_id' => $item['item_shipment_id'] ?? null,
                //                             'po_number' => $item['po_number'] ?? '',
                //                             'po_item_line' => $item['po_item_line'] ?? 0,
                //                             'po_item_code' => $item['po_item_code'] ?? '',
                //                             'material_description' => $item['material_description'] ?? '',
                //                             'quantity_confirmed' => $item['quantity_confirmed'] ?? 0,
                //                             'quantity_shipped' => $item['quantity_shipped'] ?? 0,
                //                             'quantity_arrived' => $item['quantity_arrived'] ?? 0,
                //                             'quantity_balance' => $item['quantity_balance'] ?? 0,
                //                             'unit_qty_id' => $item['unit_qty_id'] ?? null,
                //                             'cargo_readiness' => $item['cargo_readiness'] ?? null,
                //                             'delivery_address_id' => $item['delivery_address_id'] ?? 0,
                //                             'remark' => $item['remark'] ?? '-'
                //                         ];
                //                     }
                //                     $data->posShipmentItem()->createMany($detailItemShipment);
                //                 }
                //             } else {
                //                 if (!empty($value)) {
                //                     foreach ($detailPayload[3]['detail_container'] as $item) {
                //                         $detailContainer[] = [
                //                             'container_id' => $item['container_id'] ?? null,
                //                             'container_type_id' => $item['container_type_id'] ?? null,
                //                             'container_size_id' => $item['container_size_id'] ?? null,
                //                             'port_id' => $item['port_id'] ?? null,
                //                             'job_order_detail_id' => $item['job_order_detail_id'] ?? null,
                //                             'principal_depot_id' => $item['principal_depot_id'] ?? null,
                //                             'container_code' => $item['container_code'] ?? null,
                //                             'container_seal' => $item['container_seal'] ?? null,
                //                             'fmgs_start_date' => $item['fmgs_start_date'] ?? null,
                //                             'fmgs_finish_date' => $item['fmgs_finish_date'] ?? null,
                //                             'depo_in_date' => $item['depo_in_date'] ?? null,
                //                             'depo_out_date' => $item['depo_out_date'] ?? null,
                //                             'port_date' => $item['port_date'] ?? null,
                //                             'disassemble_date' => $item['disassemble_date'] ?? null,
                //                             'return_depo_date' => $item['return_depo_date'] ?? null,
                //                             'pickup_date' => $item['pickup_date'] ?? null,
                //                             'port_gatein_gate' => $item['port_gatein_gate'] ?? null,
                //                             'total_pkg' => $item['total_pkg'] ?? null,
                //                             'grossweight' => $item['grossweight'] ?? null,
                //                             'netweight' => $item['netweight'] ?? null,
                //                             'measurement' => $item['measurement'] ?? null,
                //                             'dem' => $item['dem'] ?? null,
                //                             'currency_dem_id' => $item['currency_dem_id'] ?? null,
                //                             'rep' => $item['rep'] ?? null,
                //                             'currency_rep_id' => $item['currency_rep_id'] ?? null,


                //                         ];
                //                     }

                //                     $data->preOrderContainer()->createMany($detailContainer);
                //                 }
                //             }
                //         }
                //     }
                // } else if ($action == 'addUserGroup') {

                //     if (isset($requestBody['group_user_template_id'])) {
                //         $userGroupTemplate = UserGroup::where('id', $requestBody['group_user_template_id'])->first();
                //         if ($userGroupTemplate) {
                //             $copyAccessMenu = AccessMenu::where('user_group_id', $userGroupTemplate->id)->get();
                //             $newAccessMenus = [];
                //             foreach ($copyAccessMenu as $accessMenu) {
                //                 $newAccessMenus[] = [
                //                     'user_group_id' => $data->id,
                //                     'menu_id' => $accessMenu->menu_id,
                //                     'open' => $accessMenu->open,
                //                     'add' => $accessMenu->add,
                //                     'edit' => $accessMenu->edit,
                //                     'delete' => $accessMenu->delete,
                //                     'print' => $accessMenu->print,
                //                     'approve' => $accessMenu->approve,
                //                     'disapprove' => $accessMenu->disapprove,
                //                     'reject' => $accessMenu->reject,
                //                     'close' => $accessMenu->close,
                //                 ];
                //             }
                //             // Simpan array sementara sebagai entitas baru di basis data
                //             AccessMenu::insert($newAccessMenus);

                //             //TODO: lepas remark, untuk copy dan update nama user group
                //             // $userGroupUpdate = UserGroup::find($data->id);
                //             // if ($userGroupUpdate) {
                //             //     $newName = $data->name . ' [' . $userGroupTemplate->name . ']';

                //             //     // Update nama UserGroup dengan nama baru
                //             //     $userGroupUpdate->update(['name' => $newName]);
                //             // }

                //         }
                //     }
                // }

                // INSERT NOTIFICATION

                $notificationPayload = [
                    'user_id' => auth()->user()->id,
                    'user_group_id' => auth()->user()->user_group_id,
                    'module' => 'master-data',
                    'link' => 'master-data' . '/' . $actionsToModel[$action] . '/' . $data->id,
                    'message' => 'Data' . ' ' . $actionsToModel[$action] . ' ' .  'has been added successfully',
                    'title' => 'Add data success',
                ];

                // Notification::create($notificationPayload);

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
            } else {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update($requestBody);
            }

            return $data;
        });

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

        $set_request = SetRequestGlobal($action);
        $data = $this->getData($set_request);

        if (isset($data['success'])) {
            if ($data['success'] === true) {
                // Modify each row's data to include buttons
                foreach ($data['data'] as $row) {
                    if (Route::has($request->route . '.edit')) {
                        $row->editUrl = route($request->route . '.edit', [$request->route => $row->id]);
                    }
                    if (Route::has($request->route . '.destroy')) {
                        $row->destroyUrl = route($request->route . '.destroy', [$request->route => $row->id]);
                    }
                    if (Route::has($request->route . '.show')) {
                        $row->showUrl = route($request->route . '.show', [$request->route => $row->id]);
                    }

                    // Remove the 'id' column from the row
                    if ($row->is_active || $row->is_active === 1) {
                        $row->is_active = 'Active';
                    } elseif (!$row->is_active || $row->is_active === 0) {
                        $row->is_active = 'Inactive';
                    }
                    // unset($row->id);
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
