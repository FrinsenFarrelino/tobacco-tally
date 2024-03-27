<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GlobalVariable;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GlobalController extends Controller
{
    private $globalVariable;
    protected $globalActionController;

    public function __construct()
    {
        $this->globalActionController = new GlobalActionController();
    }

    public function modelName($string)
    {
        $set = "App\\Models\\" . $string;
        return $set;
    }

    function formatCode($stringCode = "", $stringDate = "", $stringBranch = [])
    {
        $formatCode = DB::table('variables')
            ->where('code', $stringCode)
            ->first();

        $separatorSetting = '/';

        if ($formatCode) {
            $format = explode("|", $formatCode->value);
            $initial = $format[0];
            $maxDigit = $format[1];
            $dateFormat = $format[2];
            $header = $format[3];
            $type = $format[4];
            // Jika format[7] ada, gunakan nilainya, jika tidak, gunakan setting dari tabel setting
            $separator = isset($format[5]) ? $format[5] : $separatorSetting;
            $formattedCode = $initial;

            // Jika $stringDate kosong, gunakan tanggal sekarang
            $date = empty($stringDate) ? date("Y-m-d") : $stringDate;

            $dateParts = explode("-", $date);
            $year = $dateParts[0];
            $month = $dateParts[1];
            $day = $dateParts[2];
            $ym = "";

            if ($dateFormat == "ym") {
                $y = substr($year, 2, 2);
                $ym = $y . $month;
            } elseif ($dateFormat == "my") {
                $y = substr($year, 2, 2);
                $ym = $month . $y;
            } elseif ($dateFormat == "Y/m") {
                $ym = $year . "/" . $month;
            } elseif ($dateFormat == "y") {
                $y = substr($year, 2, 2);
                $ym = $y;
            }

            $branch = !empty($stringBranch['code']) ? $stringBranch['code'] : null;

            if (!empty($type)) {
                switch ($type) {
                    case "str-cab-tgl":
                        $formattedCode = $initial . $separator . $branch . $separator . $ym . $separator;
                        break;
                    case "str-tgl":
                        $formattedCode = $initial . $separator . $ym . $separator;
                        break;
                    case "cab-str":
                        $formattedCode = $branch . $separator . $initial . $separator;
                        break;
                    case "cab-str-tgl":
                        $formattedCode = $branch . $separator . $initial . $separator . $ym . $separator;
                        break;
                    case "cab-str-tgl2":
                        $formattedCode = $branch . $initial . $ym;
                        break;
                    case "cab":
                        $formattedCode = $branch . $separator;
                        break;
                    case "str":
                        $formattedCode = $initial . $separator;
                        break;
                    default:
                        $formattedCode = ''; // Handle jika tipe tidak cocok
                        break;
                }
            } elseif (!empty($date_format)) {
                $formattedCode = $branch . $separator . $initial . $separator . $ym . $separator;
            }

            $lastNumber = DB::table($header)
                ->where('id', '<>', 0)
                ->count();

            $newNumber = $lastNumber + 1;
            $newNumberDigit = strlen($newNumber);
            if ($newNumberDigit == 0) {
                $newNumberDigit = 1;
            }
            $number = "";
            for ($i = $newNumberDigit; $i < $maxDigit; $i++) {
                $number .= "0";
            }

            $formattedCode .= $number . $newNumber;

            return $formattedCode;
        } else {
            return null;
        }
    }


    public function getData($request)
    {
        $action = $request['action'];
        $actionsToModel = $this->globalActionController->getActionsToModel();
        if (!array_key_exists($action, $actionsToModel)) {
            return response()->json(['success' => false, 'message' => 'Action not found'], 404);
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
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'employees.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'employees.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'employees.deleted_by');
            $query->select(
                'employees.*',
                'positions.name as position_name',
                'divisions.name as division_name',
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

            // Save $detailPayload in a variable for use inside the closure '$result'

            $result = DB::transaction(function () use ($actionsToModel, $action, $requestBody, $detailPayload) {

                $data = $this->modelName($actionsToModel[$action])::create($requestBody);

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

            if ($action == 'updateNotification') {
                for ($i = 0; $i < $requestBody['notification_ids']; ++$i) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $requestBody['notification_ids'][$i])->update(['is_read' => 1]);
                }
                // } elseif ($action == 'updateUser') {
                //     // update grid
                //     $branchIds = [];
                //     $userGroupIds = [];
                //     $companyIds = [];

                //     foreach ($requestBody['detail'][0]['user_branch'] as $item) {
                //         if (isset($item['branch_id'])) {
                //             $branchIds[] = $item['branch_id'];
                //         }
                //     }

                //     foreach ($requestBody['detail'][1]['user_group'] as $item) {
                //         if (isset($item['user_group_id'])) {
                //             $userGroupIds[] = $item['user_group_id'];
                //         }
                //     }

                //     foreach ($requestBody['detail'][2]['user_company'] as $item) {
                //         if (isset($item['company_id'])) {
                //             $companyIds[] = $item['company_id'];
                //         }
                //     }

                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                //     if ($data) {
                //         $data->update($requestBody);

                //         // Update payload branch Ids to user_branches
                //         if (empty($branchIds)) {
                //             $data->branches()->detach();
                //         } else {
                //             $data->branches()->sync($branchIds);
                //         }

                //         // Update payload company Ids to user_companies
                //         if (empty($companyIds)) {
                //             $data->companies()->detach();
                //         } else {
                //             $data->companies()->sync($companyIds);
                //         }

                //         // Update payload user group Ids to user_group_users
                //         if (empty($userGroupIds)) {
                //             $data->user_groups()->detach();
                //         } else {
                //             $data->user_groups()->sync($userGroupIds);
                //         }
                //     }
                // } elseif ($action == 'updateUserTeam') {
                //     // update grid
                //     $details = [];

                //     if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                //         foreach ($requestBody['detail'][0]['users'] as $item) {
                //             // IF not form LNJ 2 then use ID, else use ref_id
                //             if (!$isFromLNJ2) {
                //                 $details[] = [
                //                     'id' => $item['detail_id'] ?? 0,
                //                     'user_id' => $item['user_id'],
                //                     'remark' => $item['remark'] ?? '-',
                //                 ];
                //             } else {
                //                 $details[] = [
                //                     'user_id' => $item['user_id'],
                //                     'remark' => $item['remark'] ?? '-',
                //                     'ref_id' => $item['ref_id'],
                //                 ];
                //             }
                //         }
                //     }

                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                //     if ($data) {
                //         $data->update($requestBody);

                //         if (empty($details)) {
                //             $data->userTeamDetails()->delete();
                //         } else {
                //             // To delete data if id or ref_id not in payload
                //             if (!$isFromLNJ2) {
                //                 $existingIds = collect($details)->pluck('id')->filter();
                //                 $data->userTeamDetails()->whereNotIn('id', $existingIds)->delete();
                //             } else {
                //                 $existingIds = collect($details)->pluck('ref_id')->filter();
                //                 $data->userTeamDetails()->whereNotIn('ref_id', $existingIds)->delete();
                //             }

                //             foreach ($details as $detail) {
                //                 if (!$isFromLNJ2) {
                //                     // If id exist then update if not then create new
                //                     if ($detail['id'] != 0) {
                //                         $data->userTeamDetails()->where('id', $detail['id'])->update($detail);
                //                     } else {
                //                         $newUserTeamDetail = $data->userTeamDetails()->create($detail);
                //                         $newUserTeamDetailIds[] = $newUserTeamDetail->id;
                //                     }
                //                 } else {
                //                     $affectedRows = $data->userTeamDetails()->where('ref_id', $detail['ref_id'])->update($detail);
                //                     if ($affectedRows === 0) {
                //                         // If no rows were affected, the ref_id was not found, so create a new record
                //                         $data->userTeamDetails()->create($detail);
                //                     }
                //                 }
                //             }
                //         }
                //     }
                // } elseif ($action == 'updatePrincipal') {
                //     // update grid
                //     $detailComodities = [];
                //     $detailPics = [];
                //     $detailCategories = [];
                //     $detailAddresses = [];

                //     if (isset($requestBody['detail'])) {
                //         foreach ($requestBody['detail'][0]['principal_commodity'] as $item) {
                //             $detailComodities[] = [
                //                 'id' => $item['id'],
                //                 'name' => $item['name'] ?? '-',
                //                 'imo' => $item['imo'] ?? '-',
                //                 'un' => $item['un'] ?? '-',
                //                 'pck_grp' => $item['pck_grp'] ?? '-',
                //                 'fi_pt' => $item['fi_pt'] ?? '-',
                //                 'remark' => $item['remark'] ?? '-',
                //             ];
                //         }

                //         foreach ($requestBody['detail'][1]['principal_pic'] as $item) {
                //             $detailPics[] = [
                //                 'id' => $item['id'],
                //                 'name' => $item['name'] ?? '-',
                //                 'phone' => $item['phone'] ?? '-',
                //                 'remark' => $item['remark'] ?? '-',
                //             ];
                //         }

                //         foreach ($requestBody['detail'][2]['principal_category'] as $item) {
                //             $detailCategories[] = [
                //                 'id' => $item['id'],
                //                 'principal_category_id' => $item['principal_category_id'],
                //                 'remark' => $item['remark'] ?? '-',
                //             ];
                //         }

                //         foreach ($requestBody['detail'][4]['address'] as $item) {
                //             $detailAddresses[] = [
                //                 'id' => $item['id'],
                //                 'address' => $item['address'] ?? '-',
                //                 'pic' => $item['pic'] ?? '-',
                //                 'phone' => $item['phone'] ?? '-',
                //                 'email' => $item['email'] ?? '-',
                //                 'contact' => $item['contact'] ?? '-',
                //                 'remark' => $item['remark'] ?? '-',
                //                 'note' => $item['note'] ?? '-',
                //                 'is_visible' => $item['is_visible'],
                //             ];
                //         }
                //     }

                //     // principals banks
                //     $bankNo = '';
                //     $bankName = '';
                //     if (isset($requestBody['bank_no']) && isset($requestBody['bank_name'])) {
                //         $bankNo = $requestBody['bank_no'];
                //         $bankName = $requestBody['bank_name'];
                //         unset($requestBody['bank_no']);
                //         unset($requestBody['bank_name']);
                //     }

                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                //     if ($data) {
                //         $data->update($requestBody);

                //         if ($bankNo != '' && $bankName != '') {
                //             $bankAccountPayload = [
                //                 'number' => $bankNo,
                //                 'name' => $bankName,
                //             ];

                //             $data->bankAccounts()->where('principal_id', $id)->update($bankAccountPayload);
                //         }

                //         if (empty($detailComodities)) {
                //             $data->principalCommodities()->delete();
                //         } else {
                //             $existingIds = collect($detailComodities)->pluck('id')->filter();
                //             $data->principalCommodities()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailComodities as $detail) {
                //                 if ($detail['id'] != 0 && $detail['id'] !== null) {
                //                     $data->principalCommodities()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $detail['code'] = FormattingCodeHelper::formatCode('principal_commodity_category', '', [], [], [], null);
                //                     unset($detail['id']);
                //                     // dd($detail);
                //                     $newPrincipalCommodities = $data->principalCommodities()->create($detail);
                //                 }
                //             }
                //         }

                //         if (empty($detailPics)) {
                //             $data->principalPics()->delete();
                //         } else {
                //             $existingIds = collect($detailPics)->pluck('id')->filter();
                //             $data->principalPics()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailPics as $detail) {
                //                 if ($detail['id'] != 0 && $detail['id'] !== null) {
                //                     $data->principalPics()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newPrincipalPics = $data->principalPics()->create($detail);
                //                 }
                //             }
                //         }

                //         if (empty($detailCategories)) {
                //             $data->principalCategoryDetails()->delete();
                //         } else {
                //             $existingIds = collect($detailCategories)->pluck('id')->filter();
                //             $data->principalCategoryDetails()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailCategories as $detail) {
                //                 if ($detail['id'] != 0 && $detail['id'] !== null) {
                //                     $data->principalCategoryDetails()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newPrincipalCategoryDetails = $data->principalCategoryDetails()->create($detail);
                //                 }
                //             }
                //         }

                //         if (empty($detailAddresses)) {
                //             $data->principalAddresses()->delete();
                //         } else {
                //             $existingIds = collect($detailAddresses)->pluck('id')->filter();
                //             $data->principalAddresses()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailAddresses as $detail) {
                //                 if ($detail['id'] != 0 && $detail['id'] !== null) {
                //                     $data->principalAddresses()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newPrincipalAddresses = $data->principalAddresses()->create($detail);
                //                 }
                //             }
                //         }
                //     }
                // } elseif ($action == 'updateQuotation') {
                //     // update grid
                //     $detailCargo = [];
                //     $detailBuy = [];
                //     $detailSell = [];

                //     // dd($requestBody['detail']);
                //     if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                //         if ($requestBody['detail'][0]['cargo_details']) {
                //             foreach ($requestBody['detail'][0]['cargo_details'] as $item) {
                //                 if (!$isFromLNJ2) {
                //                     $detailCargo[] = [
                //                         'id' => $item['detail_id'] ?? 0,
                //                         'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                //                         'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                //                         'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                //                         'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                //                         'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                //                         'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                //                         'quantity' => isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                //                         'total' => $item['total'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                         'container_type_id' => $item['container_type_id'] ?? null,
                //                         'container_size_id' => $item['container_size_id'] ?? null,
                //                     ];
                //                 } else {
                //                     $detailCargo[] = [
                //                         'ref_id' => $item['ref_id'] ?? 0,
                //                         'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                //                         'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                //                         'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                //                         'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                //                         'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                //                         'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                //                         'quantity' => isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                //                         'total' => $item['total'] ?? '-',
                //                         'remark' => $item['remark'] ?? '-',
                //                         'container_type_id' => $item['container_type_id'] ?? null,
                //                         'container_size_id' => $item['container_size_id'] ?? null,
                //                     ];
                //                 }
                //             }
                //         }
                //         if ($requestBody['detail'][1]['service_buy']) {
                //             foreach ($requestBody['detail'][1]['service_buy'] as $item) {
                //                 if (!$isFromLNJ2) {
                //                     $detailBuy[] = [
                //                         'id' => $item['detail_id'],
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                //                         'transaction_date' => $item['transaction_date'] ?? date('Y-m-d'),
                //                         'valid_until' => $item['valid_until'] ?? date('Y-m-d'),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'BUY',
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'service_price_id' => $item['service_price_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'costing_currency_id' => $item['costing_currency_id'] ?? null,
                //                         'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                //                     ];
                //                 } else {
                //                     $detailBuy[] = [
                //                         'ref_id' => $item['ref_id'],
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                //                         'transaction_date' => $item['transaction_date'] ?? date('Y-m-d'),
                //                         'valid_until' => $item['valid_until'] ?? date('Y-m-d'),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'BUY',
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'service_price_id' => $item['service_price_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'costing_currency_id' => $item['costing_currency_id'] ?? null,
                //                         'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                //                     ];
                //                 }
                //             }
                //         }
                //         if ($requestBody['detail'][2]['service_sell']) {
                //             foreach ($requestBody['detail'][2]['service_sell'] as $item) {
                //                 if (!$isFromLNJ2) {
                //                     $detailSell[] = [
                //                         'id' => $item['detail_id'],
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                //                         'transaction_date' => $item['transaction_date'] ?? date('Y-m-d'),
                //                         'valid_until' => $item['valid_until'] ?? date('Y-m-d'),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'SELL',
                //                         'service_id' => $item['service_id'] ?? null,
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                //                         'principal_id' => $item['principal_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                //                         'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                //                         'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                //                     ];
                //                 } else {
                //                     $detailSell[] = [
                //                         'ref_id' => $item['ref_id'],
                //                         'service_desc' => $item['service_desc'] ?? '-',
                //                         'service_price_desc' => $item['service_price_desc'] ?? '-',
                //                         'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                //                         'transaction_date' => $item['transaction_date'] ?? date('Y-m-d'),
                //                         'valid_until' => $item['valid_until'] ?? date('Y-m-d'),
                //                         'remark' => $item['remark'] ?? '-',
                //                         'charge_segment' => $item['charge_segment'] ?? '-',
                //                         'service_category' => $item['service_category'] ?? 'SELL',
                //                         'service_id' => $item['service_id'] ?? null,
                //                         'service_group_id' => $item['service_group_id'] ?? null,
                //                         'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                //                         'principal_id' => $item['principal_id'] ?? null,
                //                         'currency_id' => $item['currency_id'] ?? null,
                //                         'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                //                         'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                //                         'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                //                     ];
                //                 }
                //             }
                //         }
                //     }

                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                //     if ($data) {
                //         $data->update($requestBody);

                //         // To delete data if id or ref_id not in payload
                //         if (!$isFromLNJ2) {
                //             $existingIds = collect($detailCargo)->pluck('id')->filter();
                //             $data->rfqCargoes()->whereNotIn('id', $existingIds)->delete();
                //         } else {
                //             $existingIds = collect($detailCargo)->pluck('ref_id')->filter();
                //             $data->rfqCargoes()->whereNotIn('ref_id', $existingIds)->delete();
                //         }

                //         foreach ($detailCargo as $detail) {
                //             if (!$isFromLNJ2) {
                //                 // If id exist then update if not then create new
                //                 if ($detail['id'] != 0) {
                //                     $data->rfqCargoes()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newCargoDetail = $data->rfqCargoes()->create($detail);
                //                     $newCargoDetailIds[] = $newCargoDetail->id;
                //                 }
                //             } else {
                //                 $affectedRows = $data->rfqCargoes()->where('ref_id', $detail['ref_id'])->update($detail);
                //                 if ($affectedRows === 0) {
                //                     // If no rows were affected, the ref_id was not found, so create a new record
                //                     $data->rfqCargoes()->create($detail);
                //                 }
                //             }
                //         }

                //         // To delete data from service buy if id or ref_id not in payload
                //         if (!$isFromLNJ2) {
                //             $existingIds = collect($detailBuy)->pluck('id')->filter();
                //             $data->rfqDetails()->whereNotIn('id', $existingIds)->where('service_category', '=', 'BUY')->delete();
                //         } else {
                //             $existingIds = collect($detailBuy)->pluck('ref_id')->filter();
                //             $data->rfqDetails()->whereNotIn('ref_id', $existingIds)->where('service_category', '=', 'BUY')->delete();
                //         }

                //         foreach ($detailBuy as $detail) {
                //             if (!$isFromLNJ2) {
                //                 // If id exist then update if not then create new
                //                 if ($detail['id'] != 0) {
                //                     $data->rfqDetails()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newBuyDetail = $data->rfqDetails()->create($detail);
                //                     $newBuyDetailIds[] = $newBuyDetail->id;
                //                 }
                //             } else {
                //                 $affectedRows = $data->rfqDetails()->where('ref_id', $detail['ref_id'])->update($detail);
                //                 if ($affectedRows === 0) {
                //                     // If no rows were affected, the ref_id was not found, so create a new record
                //                     $data->rfqDetails()->create($detail);
                //                 }
                //             }
                //         }

                //         // To delete data from service sell if id or ref_id not in payload
                //         if (!$isFromLNJ2) {
                //             $existingIds = collect($detailSell)->pluck('id')->filter();
                //             $data->rfqDetails()->whereNotIn('id', $existingIds)->where('service_category', '=', 'SELL')->delete();
                //         } else {
                //             $existingIds = collect($detailSell)->pluck('ref_id')->filter();
                //             $data->rfqDetails()->whereNotIn('ref_id', $existingIds)->where('service_category', '=', 'SELL')->delete();
                //         }

                //         foreach ($detailSell as $detail) {
                //             if (!$isFromLNJ2) {
                //                 // If id exist then update if not then create new
                //                 if ($detail['id'] != 0) {
                //                     $data->rfqDetails()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newSellDetail = $data->rfqDetails()->create($detail);
                //                     $newSellDetailIds[] = $newSellDetail->id;
                //                 }
                //             } else {
                //                 $affectedRows = $data->rfqDetails()->where('ref_id', $detail['ref_id'])->update($detail);
                //                 if ($affectedRows === 0) {
                //                     // If no rows were affected, the ref_id was not found, so create a new record
                //                     $data->rfqDetails()->create($detail);
                //                 }
                //             }
                //         }
                //     }
                // } elseif ($action == 'updateStatusQuotation') {
                //     if (isset($requestBody['is_approve'])) {
                //         if ($requestBody['is_approve'] == 1) {
                //             $newData = [
                //                 'approved_by' => auth()->id(),
                //                 'is_approve' => true,
                //                 'approved_at' => now(),
                //             ];
                //         } else {
                //             $newData = [
                //                 'approved_by' => auth()->id(),
                //                 'is_approve' => false,
                //                 'approved_at' => now(),
                //             ];
                //         }
                //     } elseif (isset($requestBody['is_finish_quot'])) {
                //         if ($requestBody['is_finish_quot'] == 1) {
                //             $newData = [
                //                 'is_finish_quot' => true,
                //             ];
                //         } else {
                //             $newData = [
                //                 'is_finish_quot' => false,
                //             ];
                //         }
                //     }
                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)
                //         ->update($newData);
                // } elseif ($action == 'updatePreOrder') {
                //     // update grid
                //     $detailParties = [];
                //     $detailPackings = [];
                //     $detailItemShipment = [];

                //     if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                //         if ($requestBody['detail'][0]['detail_party']) {
                //             foreach ($requestBody['detail'][0]['detail_party'] as $item) {
                //                 $detailParties[] = [
                //                     'id' => $item['id'],
                //                     'container_type_id' => $item['container_type_id'] ?? 0,
                //                     'container_size_id' => $item['container_size_id'] ?? 0,
                //                     'qty' => $item['qty'] ?? 4,
                //                     'remark' => $item['remark'] ?? '-',
                //                 ];
                //             }
                //         }
                //         if ($requestBody['detail'][1]['detail_packing']) {
                //             foreach ($requestBody['detail'][1]['detail_packing'] as $item) {
                //                 $detailPackings[] = [
                //                     'id' => $item['id'],
                //                     'packing_detail_id' => $item['packing_detail_id'],
                //                     'container_size_id' => $item['container_size_id'] ?? 0,
                //                     'package_type' => $item['package_type'],
                //                     'packing_no' => $item['packing_no'],
                //                     'packing_size' => $item['packing_size'],
                //                     'packing_type' => $item['packing_type'] ?? '',
                //                     'uom' => $item['uom'] ?? '',
                //                     'qty' => $item['qty'] ?? '',
                //                     'seal_no' => $item['seal_no'] ?? '',
                //                     'etd_factory' => $item['etd_factory'] ?? '',
                //                     'parent_id' => $item['parent_id'] ?? null,
                //                     'shipment_item_id' => $item['shipment_item_id'] ?? null,
                //                     'is_in_container' => $item['is_in_container'] ?? false,
                //                     'remark' => $item['remark'] ?? '-',
                //                 ];
                //             }
                //         }
                //         if ($requestBody['detail'][2]['detail_item_shipment']) {
                //             foreach ($requestBody['detail'][2]['detail_item_shipment'] as $item) {
                //                 $detailItemShipment[] = [
                //                     'id' => $item['id'],
                //                     'item_shipment_id' => $item['item_shipment_id'] ?? null,
                //                     'po_number' => $item['po_number'] ?? '',
                //                     'po_item_line' => $item['po_item_line'] ?? 0,
                //                     'po_item_code' => $item['po_item_code'] ?? '',
                //                     'material_description' => $item['material_description'] ?? '',
                //                     'quantity_confirmed' => $item['quantity_confirmed'] ?? 0,
                //                     'quantity_shipped' => $item['quantity_shipped'] ?? 0,
                //                     'quantity_arrived' => $item['quantity_arrived'] ?? 0,
                //                     'quantity_balance' => $item['quantity_balance'] ?? 0,
                //                     'unit_qty_id' => $item['unit_qty_id'] ?? 0,
                //                     'cargo_readiness' => $item['cargo_readiness'] ?? '',
                //                     'delivery_address_id' => $item['delivery_address_id'] ?? 0,
                //                     'remark' => $item['remark'] ?? '-',
                //                 ];
                //             }
                //         }
                //     }

                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                //     if ($data) {
                //         $data->update($requestBody);

                //         if (empty($detailParties)) {
                //             $data->preOrderParty()->delete();
                //         } else {
                //             $existingIds = collect($detailParties)->pluck('id')->filter();
                //             $data->preOrderParty()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailParties as $detail) {
                //                 if ($detail['id'] != 0) {
                //                     $data->preOrderParty()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newDetailParties = $data->preOrderParty()->create($detail);
                //                     // $newDetailParties[] = $newDetailParties->id;
                //                 }
                //             }
                //         }

                //         if (empty($detailPackings)) {
                //             $data->posPackingDetail()->delete();
                //         } else {
                //             $existingIds = collect($detailPackings)->pluck('id')->filter();
                //             $data->posPackingDetail()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailPackings as $detail) {
                //                 if ($detail['id'] != 0) {
                //                     $data->posPackingDetail()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newDetailPackings = $data->posPackingDetail()->create($detail);
                //                     // $newDetailPackings[] = $newDetailPackings->id;
                //                 }
                //             }
                //         }

                //         if (empty($detailItemShipment)) {
                //             $data->posShipmentItem()->delete();
                //         } else {
                //             $existingIds = collect($detailItemShipment)->pluck('id')->filter();
                //             $data->posShipmentItem()->whereNotIn('id', $existingIds)->delete();
                //             foreach ($detailItemShipment as $detail) {
                //                 if ($detail['id'] != 0) {
                //                     $data->posShipmentItem()->where('id', $detail['id'])->update($detail);
                //                 } else {
                //                     $newDetailItemShipment = $data->posShipmentItem()->create($detail);
                //                     // $newDetailItemShipment[] = $newDetailItemShipment->id;
                //                 }
                //             }
                //         }
                //     }
                // } elseif ($action == 'approvePreOrder') {
                //     $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update([
                //         'approved_by' => auth()->id(),
                //         'is_approve' => true,
                //         'approved_at' => now(),
                //     ]);
                // } elseif ($action == 'dissaprovePreOrder') {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update([
                    'rejected_by' => auth()->id(),
                    'is_approve' => false,
                    'rejected_at' => now(),
                ]);
            } else {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update($requestBody);
            }

            return $data;
        });

        // Get id dari yang baru dicreate buat dikirim ke lnj 2 sebagai ref_id
        if ($action == 'updateUserTeam') {
            $newUserTeamDetailIds = $data->userTeamDetails()->pluck('id')->toArray();
        } elseif ($action == 'updateQuotation') {
            $newCargoDetailIds = $data->rfqCargoes()->pluck('id')->toArray();
            $newBuyDetailIds = $data->rfqDetails()->where('service_category', '=', 'BUY')->pluck('id')->toArray();
            $newSellDetailIds = $data->rfqDetails()->where('service_category', '=', 'SELL')->pluck('id')->toArray();
        } elseif ($action == 'updatePreOrder') {
            // TODO:buat ketika syncronisasi
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
            // $filter = $request->get('filter');
        }
        if ($request->get('input_param')) {
            $input_param = json_decode($request->get('filter'), true);
            // $input_param = $request->get('filter');
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

    public function switchLang($lang, Request $request)
    {
        $getUrlPrev = URL::previous();

        if (array_key_exists($lang, Config::get('languages'))) {
            Session::put('applocale', $lang);
        }

        return redirect($getUrlPrev);
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
