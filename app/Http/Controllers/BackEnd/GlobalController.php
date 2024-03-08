<?php

namespace App\Http\Controllers\BackEnd;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\ActionEnum;
use App\Helpers\FormattingCodeHelper;
use App\Http\Controllers\GlobalActionController;
use App\Http\Services\PreOrderGridService;
use App\Http\Services\PrincipalDataGridService;
use App\Models\Notification;
use App\Models\Note;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\ToArray;
use App\Http\Services\UserGridService;
use App\Http\Services\UserTeamGridService;
use App\Http\Services\QuotationGridService;
use App\Models\AccessMaster;
use App\Models\AccessMenu;
use App\Models\Branch;
use App\Models\Principal;
use App\Models\PrincipalBankAccount;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


use function GuzzleHttp\json_decode;
use function Laravel\Prompts\error;

class GlobalController extends Controller
{

    protected $globalActionController;
    protected $userGridService;
    protected $principalDataGridService;
    protected $userTeamGridService;
    protected $quotationGridService;
    protected $preOrderGridService;

    public function __construct(
        GlobalActionController $globalActionController,
        UserGridService $userGridService,
        PrincipalDataGridService $principalDataGridService,
        UserTeamGridService $userTeamGridService,
        QuotationGridService $quotationGridService,
        PreOrderGridService $preOrderGridService,

    ) {
        $this->globalActionController = $globalActionController;
        $this->userGridService = $userGridService;
        $this->principalDataGridService = $principalDataGridService;
        $this->userTeamGridService = $userTeamGridService;
        $this->quotationGridService = $quotationGridService;
        $this->preOrderGridService = $preOrderGridService;
    }

    public function modelName($string)
    {
        $set = "App\\Models\\" . $string;
        return $set;
    }

    public function generateRfqDetailServiceId($rfqDetailId)
    {
        $nomorServiceId = DB::table(DB::raw("(SELECT a.id AS id_rfq_detail, b.id AS id_service, a.service_id, b.name, a.service_desc
                                                FROM rfq_details a
                                                JOIN services b ON a.service_desc LIKE CONCAT(b.name,'%')
                                                WHERE a.id = $rfqDetailId
                                                ORDER BY a.id, b.name DESC) a"))
            ->groupBy('a.id_rfq_detail', 'id_service')
            ->select('id_service')
            ->first();

        return $nomorServiceId->id_service;
    }

    public function generateRfqDetailServicePriceId($rfqDetailId)
    {
        $nomorServiceId = DB::table(DB::raw("(SELECT a.id AS id_rfq_detail, b.id AS id_service, a.service_id, b.name, a.service_desc
                                                FROM rfq_details a
                                                JOIN service_prices b ON a.service_desc LIKE CONCAT(b.name,'%')
                                                WHERE a.id = $rfqDetailId
                                                ORDER BY a.id, b.name DESC) a"))
            ->groupBy('a.id_rfq_detail', 'id_service')
            ->select('id_service')
            ->first();

        return $nomorServiceId;
    }

    public function getEnum($type_name)
    {
        $query = "SELECT enumlabel FROM pg_enum
            WHERE enumtypid = (
              SELECT oid FROM pg_type WHERE typname = '" . $type_name . "'
            );";
        $result = DB::select($query);

        if (!empty($result)) {
            return $this->sendResponse(true, 200, $result);
        }

        return $this->sendResponse(false, Response::HTTP_NOT_FOUND, []);
    }

    /**
     * Display a listing of the resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $actionsToModel = $this->globalActionController->getActionsToModel();
        // get action in request
        $action = $request->action;
        // get request body
        $requestBody = $request->requestData;
        // get detail request payload for grid
        if (isset($requestBody['detail'])) {
            $detailPayload = $requestBody['detail'];
        } else {
            $detailPayload = '';
        }


        if (!array_key_exists($action, $actionsToModel)) {
            return $this->sendResponse(false, Response::HTTP_NOT_FOUND, 'Action Not Found!');
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
            if ($action == 'addQuotation') {
                if (isset($requestBody['is_customs_clearance']) && $requestBody['is_customs_clearance']) {
                    $requestBody['format_code'] = 'quotation_customClearance';
                } else if (isset($requestBody['is_freight']) && $requestBody['is_freight']) {
                    $requestBody['format_code'] = 'quotation_freight';
                } else if (isset($requestBody['is_other']) && $requestBody['is_other']) {
                    $requestBody['format_code'] = 'quotation_others';
                } else if (isset($requestBody['is_shipping_agency']) && $requestBody['is_shipping_agency']) {
                    $requestBody['format_code'] = 'quotation_shippingAgency';
                } else {
                    $requestBody['format_code'] = 'quotation';
                }

                if (isset($requestBody['trade'])) {
                    if ($requestBody['trade'] == 'export') {
                        $requestBody['format_code'] .= '_export';
                    } else if ($requestBody['trade'] == 'import') {
                        $requestBody['format_code'] .= '_import';
                    } else if ($requestBody['trade'] == 'domestic') {
                        $requestBody['format_code'] .= '_domestic';
                    } else if ($requestBody['trade'] == 'xbook') {
                        $requestBody['format_code'] .= '_xbook';
                    } else if ($requestBody['trade'] == 'repo') {
                        $requestBody['format_code'] .= '_repo';
                    }
                }
                $requestBody['request_date'] = $requestBody['request_date'] ?? date("Y-m-d");
                $requestBody['commodity_desc'] = $requestBody['commodity_desc'] ?? '-';
                $requestBody['term_of_payment'] = $requestBody['term_of_payment'] ?? 0;
                $requestBody['quantity'] = $requestBody['quantity'] ?? '-';
                $requestBody['weight'] = $requestBody['weight'] ?? '-';
                $requestBody['measurement'] = $requestBody['measurement'] ?? '-';
                $requestBody['imoun'] = $requestBody['imoun'] ?? '-';
                $requestBody['free_time'] = $requestBody['free_time'] ?? 0;
                $requestBody['is_finish'] = 1;
                // generate Branch code
                $requestBody['branch_code'] = Branch::where('id', $requestBody['branch_id'])->first();

                if (isset($detailPayload[0]['notes'])) {
                    foreach ($detailPayload[0]['notes'] as $item) {
                        $requestBody['main_notes'] = $item;
                    }
                } else {
                    $requestBody['main_notes'] = $requestBody['main_notes'] ?? '-';
                }
                $requestBody['imoun'] = $requestBody['imoun'] ?? '';
            } else if ($action == 'addRemark' && !isset($requestBody['is_lnj2'])) {
                // generate Branch code
                $requestBody['branch_code'] = Branch::where('id', $requestBody['branch_id'])->first();
            } else if ($action == 'addNote') {
                $match = Note::where('content', 'ILIKE', $requestBody['content'])->whereNull('deleted_at')->exists();
                if ($match) {
                    return $this->sendResponse(false, Response::HTTP_INTERNAL_SERVER_ERROR, 'Content input are same, please input other content.');
                }
            }

            // format code is required
            // generate code
            $formattedCode = FormattingCodeHelper::formatCode($requestBody['format_code'] ?? '', "", [], $action == 'addQuotation' || ($action == 'addRemark' && !isset($requestBody['is_lnj2'])) ? ['code' => $requestBody['branch_code']['code']] : [], [], $requestBody['prefix'] ?? null);
        }

        // code: formattedCode | manualCode
        $payload = [
            'code' => $formattedCode ?? $requestBody['manual_code'] ?? '',
            'password' => $password ?? '',
            'created_by' => auth()->id(),
        ] + $requestBody;

        // dd($payload);

        try {

            // Save $detailPayload in a variable for use inside the closure '$result'

            $result = DB::transaction(function () use ($actionsToModel, $action, $payload, $detailPayload) {

                $data = $this->modelName($actionsToModel[$action])::create($payload);

                if ($action == 'addUser') {
                    $lastInsertedId = $data->id;
                    // insert detail payload 'user_branch' into user_branches
                    if (!empty($detailPayload[0]['user_branch'])) {
                        $branchIds = [];

                        foreach ($detailPayload[0]['user_branch'] as $item) {
                            if (isset($item['branch_id'])) {
                                $branchIds[] = $item['branch_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['branch_id'],
                                'relation_type' => 'master_branch',
                                'relation_table' => 'branches',
                                'remark' => $item['remark'],
                            ]);
                        }

                        $data->branches()->attach($branchIds);
                    }

                    if (!empty($detailPayload[1]['user_branch_report'])) {
                        $branchReportIds = [];

                        foreach ($detailPayload[1]['user_branch_report'] as $item) {

                            if (isset($item['branch_id'])) {
                                $branchReportIds[] = $item['branch_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['branch_id'],
                                'relation_type' => 'master_branch_report',
                                'relation_table' => 'branches',
                                'remark' => $item['remark'],
                            ]);
                        }
                    }

                    if (!empty($detailPayload[2]['user_group'])) {
                        $userGroupIds = [];

                        foreach ($detailPayload[2]['user_group'] as $item) {
                            if (isset($item['user_group_id'])) {
                                $userGroupIds[] = $item['user_group_id'];
                            }
                        }

                        $data->user_groups()->attach($userGroupIds);
                    }

                    if (!empty($detailPayload[3]['user_company'])) {
                        $companyIds = [];

                        foreach ($detailPayload[3]['user_company'] as $item) {
                            if (isset($item['company_id'])) {
                                $companyIds[] = $item['company_id'];
                            }
                        }

                        $data->companies()->attach($companyIds);
                    }

                    if (!empty($detailPayload[4]['access_application'])) {

                        $accessApplicationIds = [];

                        foreach ($detailPayload[4]['access_application'] as $item) {
                            if (isset($item['webstite_access_id'])) {
                                $accessApplicationIds[] = $item['webstite_access_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['webstite_access_id'],
                                'relation_type' => 'master_website',
                                'relation_table' => 'websites',
                                'remark' => $item['remark'],
                            ]);
                        }
                    }

                    if (!empty($detailPayload[5]['access_file_principal'])) {
                        $accessPrincipalFileIds = [];

                        foreach ($detailPayload[5]['access_file_principal'] as $item) {
                            if (isset($item['principal_file_access_id'])) {
                                $accessPrincipalFileIds[] = $item['principal_file_access_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['principal_file_access_id'],
                                'relation_type' => 'master_principal_file',
                                'relation_table' => 'principals',
                                'remark' => $item['remark'],
                            ]);
                        }
                    }

                    if (!empty($detailPayload[6]['access_file_principal_group'])) {
                        $accessPrincipalGroupFileIds = [];

                        foreach ($detailPayload[6]['access_file_principal_group'] as $item) {
                            if (isset($item['principal_group_access_file_id'])) {
                                $accessPrincipalGroupFileIds[] = $item['principal_group_access_file_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['principal_group_access_file_id'],
                                'relation_type' => 'master_principal_group_file',
                                'relation_table' => 'principal_groups',
                                'remark' => $item['remark'],
                            ]);
                        }
                    }

                    if (!empty($detailPayload[7]['access_webbooking_principal_group'])) {
                        $accessPrincipalGroupIds = [];

                        foreach ($detailPayload[7]['access_webbooking_principal_group'] as $item) {
                            if (isset($item['principal_group_web_access_id'])) {
                                $accessPrincipalGroupIds[] = $item['principal_group_web_access_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['principal_group_web_access_id'],
                                'relation_type' => 'master_principal_group',
                                'relation_table' => 'principal_groups',
                                'remark' => $item['remark'],
                            ]);
                        }
                    }

                    if (!empty($detailPayload[8]['access_doc_dist'])) {
                        $accessDocumentDistributionIds = [];

                        foreach ($detailPayload[8]['access_doc_dist'] as $item) {
                            if (isset($item['doc_access_id'])) {
                                $accessDocumentDistributionIds[] = $item['doc_access_id'];
                            }

                            AccessMaster::create([
                                'user_id' => $lastInsertedId,
                                'relation_id' => $item['doc_access_id'],
                                'relation_type' => 'master_document_distribution',
                                'relation_table' => 'document_distributions',
                                'remark' => $item['remark'],
                            ]);
                        }
                    }
                } else if ($action == 'addUserTeam') {
                    // dd($detailPayload);
                    if ($detailPayload != '' && $detailPayload != []) {
                        foreach ($detailPayload[0]['users'] as $item) {
                            if (isset($payload['is_lnj2'])) {
                                $details[] = [
                                    'user_id' => $item['user_id'],
                                    'remark' => $item['remark'] ?? '-',
                                    'ref_id' => $item['ref_id']
                                ];
                            } else {
                                $details[] = [
                                    'user_id' => $item['user_id'],
                                    'remark' => $item['remark'] ?? '-'
                                ];
                            }
                        }
                        $createdDetails = $data->userTeamDetails()->createMany($details);
                        // Collect IDs of the inserted records
                        $UserTeamDetailIds = $createdDetails->pluck('id')->toArray();
                    }
                } else if ($action == 'addPrincipal') {
                    try {
                        try {
                            $principalBlacklistPayload = [
                                'is_active' => true,
                            ];

                            $data->principalBlacklists()->create($principalBlacklistPayload);
                        } catch (\Exception $e) {
                            $timestamp = date('Y-m-d H:i:s');
                            $errorMessage = $e->getMessage();
                            $logEntry = "$timestamp - $errorMessage\n";
                            File::append(storage_path('logs/error.log'), $logEntry);
                        }

                        if (isset($payload["bank_no"]) && isset($payload["bank_name"])) {
                            if ($payload["bank_no"] != "" && $payload["bank_name"] != null) {
                                $bankAccountPayload = [
                                    'number' => $payload["bank_no"],
                                    'name' => $payload["bank_name"],
                                ];
                                $data->bankAccounts()->create($bankAccountPayload);
                            }
                        }


                        if ($detailPayload != '' && $detailPayload != []) {
                            $detailCommodities = [];

                            $detailPayloadCommodities = $detailPayload[0]['principal_commodity'];
                            $formattedCode = FormattingCodeHelper::formatCode('principal_commodity_category', "", [], [], [], null);


                            if ($detailPayloadCommodities != []) {
                                foreach ($detailPayloadCommodities as $i => $item) {

                                    $detailCommodities[] = [
                                        'name' => $item['name'] ?? '-',
                                        'imo' => $item['imo'] ?? '-',
                                        'un' => $item['un'] ?? '-',
                                        'pck_grp' => $item['pck_grp'] ?? '-',
                                        'fi_pt' => $item['fi_pt'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'code' =>  $formattedCode,
                                    ];
                                }

                                $createdPrincipalCommodities = $data->principalCommodities()->createMany($detailCommodities);
                                $principalCommoditiesDetailIds = $createdPrincipalCommodities->pluck('id')->toArray();
                            }

                            if ($detailPayload[1]['principal_pic'] != []) {
                                foreach ($detailPayload[1]['principal_pic'] as $item) {
                                    $detailPics[] = [
                                        'name' => $item['name'] ?? '-',
                                        'phone' => $item['phone'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                    ];
                                }
                                $createdPrincipalPics = $data->principalPics()->createMany($detailPics);
                                $principalPicsIds = $createdPrincipalPics->pluck('id')->toArray();
                            }

                            if ($detailPayload[2]['principal_category'] != []) {
                                foreach ($detailPayload[2]['principal_category'] as $item) {
                                    $detailCategories[] = [
                                        'principal_category_id' => $item['principal_category_id'],
                                        'remark' => $item['remark'] ?? '-',
                                    ];
                                }
                                $createdPrincipalCategories = $data->principalCategoryDetails()->createMany($detailCategories);
                                $principalCategoriesIds = $createdPrincipalCategories->pluck('id')->toArray();
                            }


                            // //TODO add service from supplier [3]

                            if ($detailPayload[4]['address'] != []) {
                                foreach ($detailPayload[4]['address'] as $item) {
                                    $detailAddresses[] = [
                                        'address' => $item['address']  ?? '-',
                                        'pic' => $item['pic']  ?? '-',
                                        'phone' => $item['phone']  ?? '-',
                                        'email' => $item['email']  ?? '-',
                                        'contact' => $item['contact'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'note' => $item['note'] ?? '-',
                                        'is_visible' => $item['is_visible'],
                                        'district_id' => $item['district_id'],
                                    ];
                                }
                                $createdPrincipalAddresses = $data->principalAddresses()->createMany($detailAddresses);
                                $principalAddressesIds = $createdPrincipalAddresses->pluck('id')->toArray();
                            }
                        }
                    } catch (\Exception $e) {
                        $timestamp = date('Y-m-d H:i:s');
                        $errorMessage = $e->getMessage();
                        $logEntry = "$timestamp - $errorMessage\n";
                        File::append(storage_path('logs/error.log'), $logEntry);
                    }
                } else if ($action == 'addQuotation') {
                    if ($detailPayload != '' && $detailPayload != []) {
                        if ($detailPayload[0]['cargo_details']) {
                            if (isset($payload['is_lnj2'])) {
                                foreach ($detailPayload[0]['cargo_details'] as $item) {
                                    $detailCargo[] = [
                                        'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                                        'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                                        'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                                        'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                                        'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                                        'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                                        'quantity' =>  isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                                        'total' => $item['total'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'container_type_id' => $item['container_type_id'] ?? null,
                                        'container_size_id' => $item['container_size_id'] ?? null,
                                        'ref_id' => $item['ref_id']
                                    ];
                                }
                            } else {
                                foreach ($detailPayload[0]['cargo_details'] as $item) {
                                    $detailCargo[] = [
                                        'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                                        'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                                        'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                                        'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                                        'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                                        'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                                        'quantity' =>  isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                                        'total' => $item['total'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'container_type_id' => $item['container_type_id'] ?? null,
                                        'container_size_id' => $item['container_size_id'] ?? null
                                    ];
                                }
                            }

                            $createdCargoDetails = $data->rfqCargoes()->createMany($detailCargo);
                            // Collect IDs of the inserted records
                            $rfqCargoDetailIds = $createdCargoDetails->pluck('id')->toArray();
                        }

                        if ($detailPayload[1]['service_buy']) {
                            if (isset($payload['is_lnj2'])) {
                                foreach ($detailPayload[1]['service_buy'] as $item) {
                                    $detailBuy[] = [
                                        'service_desc' => $item['service_desc'] ?? '-',
                                        'service_price_desc' => $item['service_price_desc'] ?? '-',
                                        'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                                        'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                        'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                        'remark' => $item['remark'] ?? '-',
                                        'charge_segment' => $item['charge_segment'] ?? '-',
                                        'service_category' => $item['service_category'] ?? 'BUY',
                                        'service_group_id' => $item['service_group_id'] ?? null,
                                        'principal_id' => $item['principal_id'] ?? null,
                                        'service_price_id' => $item['service_price_id'] ?? null,
                                        'currency_id' => $item['currency_id'] ?? null,
                                        'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                        'service_id' => $item['service_id'] ?? null,
                                        'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                                        'ref_id' => $item['ref_id']
                                    ];
                                }
                            } else {
                                foreach ($detailPayload[1]['service_buy'] as $item) {
                                    $detailBuy[] = [
                                        'service_desc' => $item['service_desc'] ?? '-',
                                        'service_price_desc' => $item['service_price_desc'] ?? '-',
                                        'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                                        'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                        'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                        'remark' => $item['remark'] ?? '-',
                                        'charge_segment' => $item['charge_segment'] ?? '-',
                                        'service_category' => $item['service_category'] ?? 'BUY',
                                        'service_group_id' => $item['service_group_id'] ?? null,
                                        'service_price_id' => $item['service_price_id'] ?? null,
                                        'principal_id' => $item['principal_id'] ?? null,
                                        'currency_id' => $item['currency_id'] ?? null,
                                        'service_id' => $item['service_id'] ?? null,
                                        'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                        'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                                    ];
                                }
                            }
                            $createdServiceBuyDetails = $data->rfqDetails()->createMany($detailBuy);
                            // Collect IDs of the inserted records
                            $rfqServiceBuyDetailIds = $createdServiceBuyDetails->pluck('id')->toArray();
                        }

                        if ($detailPayload[2]['service_sell']) {
                            if (isset($payload['is_lnj2'])) {
                                foreach ($detailPayload[2]['service_sell'] as $item) {
                                    $detailSell[] = [
                                        'service_desc' => $item['service_desc'] ?? '-',
                                        'service_price_desc' => $item['service_price_desc'] ?? '-',
                                        'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                                        'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                        'service_group_id' => $item['service_group_id'] ?? null,
                                        'service_price_id' => $item['service_price_id'] ?? null,
                                        'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                        'remark' => $item['remark'] ?? '-',
                                        'charge_segment' => $item['charge_segment'] ?? '-',
                                        'service_category' => $item['service_category'] ?? 'SELL',
                                        'service_id' => $item['service_id'] ?? null,
                                        'principal_id' => $item['principal_id'] ?? null,
                                        'currency_id' => $item['currency_id'] ?? null,
                                        'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                                        'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                                        'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                                        'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                                        'ref_id' => $item['ref_id']
                                    ];
                                }
                            } else {
                                foreach ($detailPayload[2]['service_sell'] as $item) {
                                    $detailSell[] = [
                                        'service_desc' => $item['service_desc'] ?? '-',
                                        'service_price_desc' => $item['service_price_desc'] ?? '-',
                                        'supplier_service_id' => isset($item['supplier_service_id']) ?? null,
                                        'service_group_id' => $item['service_group_id'] ?? null,
                                        'service_price_id' => $item['service_price_id'] ?? null,
                                        'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                        'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                        'remark' => $item['remark'] ?? '-',
                                        'charge_segment' => $item['charge_segment'] ?? '-',
                                        'service_category' => $item['service_category'] ?? 'SELL',
                                        'service_id' => $item['service_id'] ?? null,
                                        'principal_id' => $item['principal_id'] ?? null,
                                        'currency_id' => $item['currency_id'] ?? null,
                                        'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                                        'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                                        'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                                        'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                                    ];
                                }
                            }

                            $createdServiceSellDetails = $data->rfqDetails()->createMany($detailSell);
                            // Collect IDs of the inserted records
                            $rfqServiceSellDetailIds = $createdServiceSellDetails->pluck('id')->toArray();
                        }
                    }
                } else if ($action == 'addPreOrder') {
                    if ($detailPayload != '') {

                        $detailParties = [];
                        $detailPackings = [];
                        $detailItemShipment = [];
                        $detailContainer = [];

                        foreach ($detailPayload as $value) {

                            if (array_key_exists('detail_party', $value)) {

                                if (!empty($value)) {
                                    foreach ($detailPayload[0]['detail_party'] as $item) {
                                        $detailParties[] = [
                                            'container_type_id' => $item['container_type_id'] ?? 0,
                                            'container_size_id' => $item['container_size_id'] ?? 0,
                                            'qty' => $item['qty'] ?? 4,
                                            'remark' => $item['remark'] ?? '-'
                                        ];
                                    }
                                    $data->preOrderParty()->createMany($detailParties);
                                }
                            } elseif (array_key_exists('detail_packing', $value)) {
                                if (!empty($value)) {
                                    foreach ($detailPayload[1]['detail_packing'] as $item) {
                                        $detailPackings[] = [
                                            'packing_detail_id' => $item['packing_detail_id'] ?? null,
                                            'container_size_id' => $item['container_size_id'] ?? null,
                                            'package_type' => $item['package_type'] ?? 'C',
                                            'packing_no' => $item['packing_no'] ?? '',
                                            'packing_size' => $item['packing_size'] ?? '',
                                            'packing_type' => $item['packing_type'] ?? '',
                                            'uom' => $item['uom'] ?? '',
                                            'qty' => $item['qty'] ?? null,
                                            'seal_no' => $item['seal_no'] ?? '',
                                            'etd_factory' => $item['etd_factory'] ?? now(),
                                            'parent_id' => $item['parent_id'] ?? null,
                                            'shipment_item_id' => $item['shipment_item_id'] ?? null,
                                            'is_in_container' => $item['is_in_container'] ?? false,
                                            'remark' => $item['remark'] ?? '-'
                                        ];
                                    }
                                    $data->posPackingDetail()->createMany($detailPackings);
                                }
                            } elseif (array_key_exists('detail_item_shipment', $value)) {
                                if (!empty($value)) {
                                    foreach ($detailPayload[2]['detail_item_shipment'] as $item) {
                                        $detailItemShipment[] = [
                                            'item_shipment_id' => $item['item_shipment_id'] ?? null,
                                            'po_number' => $item['po_number'] ?? '',
                                            'po_item_line' => $item['po_item_line'] ?? 0,
                                            'po_item_code' => $item['po_item_code'] ?? '',
                                            'material_description' => $item['material_description'] ?? '',
                                            'quantity_confirmed' => $item['quantity_confirmed'] ?? 0,
                                            'quantity_shipped' => $item['quantity_shipped'] ?? 0,
                                            'quantity_arrived' => $item['quantity_arrived'] ?? 0,
                                            'quantity_balance' => $item['quantity_balance'] ?? 0,
                                            'unit_qty_id' => $item['unit_qty_id'] ?? null,
                                            'cargo_readiness' => $item['cargo_readiness'] ?? null,
                                            'delivery_address_id' => $item['delivery_address_id'] ?? 0,
                                            'remark' => $item['remark'] ?? '-'
                                        ];
                                    }
                                    $data->posShipmentItem()->createMany($detailItemShipment);
                                }
                            } else {
                                if (!empty($value)) {
                                    foreach ($detailPayload[3]['detail_container'] as $item) {
                                        $detailContainer[] = [
                                            'container_id' => $item['container_id'] ?? null,
                                            'container_type_id' => $item['container_type_id'] ?? null,
                                            'container_size_id' => $item['container_size_id'] ?? null,
                                            'port_id' => $item['port_id'] ?? null,
                                            'job_order_detail_id' => $item['job_order_detail_id'] ?? null,
                                            'principal_depot_id' => $item['principal_depot_id'] ?? null,
                                            'container_code' => $item['container_code'] ?? null,
                                            'container_seal' => $item['container_seal'] ?? null,
                                            'fmgs_start_date' => $item['fmgs_start_date'] ?? null,
                                            'fmgs_finish_date' => $item['fmgs_finish_date'] ?? null,
                                            'depo_in_date' => $item['depo_in_date'] ?? null,
                                            'depo_out_date' => $item['depo_out_date'] ?? null,
                                            'port_date' => $item['port_date'] ?? null,
                                            'disassemble_date' => $item['disassemble_date'] ?? null,
                                            'return_depo_date' => $item['return_depo_date'] ?? null,
                                            'pickup_date' => $item['pickup_date'] ?? null,
                                            'port_gatein_gate' => $item['port_gatein_gate'] ?? null,
                                            'total_pkg' => $item['total_pkg'] ?? null,
                                            'grossweight' => $item['grossweight'] ?? null,
                                            'netweight' => $item['netweight'] ?? null,
                                            'measurement' => $item['measurement'] ?? null,
                                            'dem' => $item['dem'] ?? null,
                                            'currency_dem_id' => $item['currency_dem_id'] ?? null,
                                            'rep' => $item['rep'] ?? null,
                                            'currency_rep_id' => $item['currency_rep_id'] ?? null,


                                        ];
                                    }

                                    $data->preOrderContainer()->createMany($detailContainer);
                                }
                            }
                        }
                    }
                } else if ($action == 'addUserGroup') {

                    if (isset($payload['group_user_template_id'])) {
                        $userGroupTemplate = UserGroup::where('id', $payload['group_user_template_id'])->first();
                        if ($userGroupTemplate) {
                            $copyAccessMenu = AccessMenu::where('user_group_id', $userGroupTemplate->id)->get();
                            $newAccessMenus = [];
                            foreach ($copyAccessMenu as $accessMenu) {
                                $newAccessMenus[] = [
                                    'user_group_id' => $data->id,
                                    'menu_id' => $accessMenu->menu_id,
                                    'open' => $accessMenu->open,
                                    'add' => $accessMenu->add,
                                    'edit' => $accessMenu->edit,
                                    'delete' => $accessMenu->delete,
                                    'print' => $accessMenu->print,
                                    'approve' => $accessMenu->approve,
                                    'disapprove' => $accessMenu->disapprove,
                                    'reject' => $accessMenu->reject,
                                    'close' => $accessMenu->close,
                                ];
                            }
                            // Simpan array sementara sebagai entitas baru di basis data
                            AccessMenu::insert($newAccessMenus);

                            //TODO: lepas remark, untuk copy dan update nama user group
                            // $userGroupUpdate = UserGroup::find($data->id);
                            // if ($userGroupUpdate) {
                            //     $newName = $data->name . ' [' . $userGroupTemplate->name . ']';

                            //     // Update nama UserGroup dengan nama baru
                            //     $userGroupUpdate->update(['name' => $newName]);
                            // }

                        }
                    }
                }

                // INSERT NOTIFICATION

                $notificationPayload = [
                    'user_id' => auth()->user()->id,
                    'user_group_id' => auth()->user()->user_group_id,
                    'module' => 'master-data',
                    'link' => 'master-data' . '/' . $actionsToModel[$action] . '/' . $data->id,
                    'message' => 'Data' . ' ' . $actionsToModel[$action] . ' ' .  'has been added successfully',
                    'title' => 'Add data success',
                ];

                Notification::create($notificationPayload);

                // send data to LNJ V2 || if not origin from LNJ2 then send to LNJ 2
                if (!isset($payload['is_lnj2'])) {
                    if ($data) {
                        $baseSyncUrl = env('SYNC_BASE_URL');
                        $model =  lcfirst($actionsToModel[$action]);

                        // Note: jika data origin dari LNJ2 maka tidak perlu ada update ini counter 
                        // karena harus di update diluar proses save di lnj3
                        // Update counter di table variables

                        if ($action == 'addPrincipal') {
                            DB::table('variables')
                                ->where('label', 'principal')
                                ->increment('counter');
                        } else {
                            DB::table('variables')
                                ->where('label', $model)
                                ->increment('counter');
                        }



                        $dataToLnj2 = $data->toArray();

                        if ($action == 'addUser') {
                            $dataToLnj2['password'] = md5($payload['password']);
                        }

                        if ($action == 'addPrincipal') {
                            $dataToLnj2['bank_no'] = $payload['bank_no'] ?? '';
                            $dataToLnj2['bank_name'] = $payload['bank_name'] ?? '';
                        }

                        $objPayload = [
                            'data' => $dataToLnj2,
                            'endpoint_sync' => $baseSyncUrl . 'toLNJIS2/master/create/' . $model,
                        ];


                        if ($action == 'addUser') {
                            $accessBranchDetail = [];
                            $accessBranchReportDetail = [];
                            $accessApplicationDetail = [];
                            $accessFilePrincipalDetail = [];
                            $accessFilePrincipalGroupDetail = [];
                            $accessWebbookingPrincipalGroupDetail = [];
                            $accessDocDistDetail = [];


                            if (!empty($detailPayload[0]['user_branch'])) {

                                foreach ($detailPayload[0]['user_branch'] as $item) {
                                    if (isset($item['branch_id'])) {
                                        $accessBranchDetail[] = [
                                            'branch_id' => $item['branch_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }

                            if (!empty($detailPayload[1]['user_branch_report'])) {

                                foreach ($detailPayload[1]['user_branch_report'] as $item) {
                                    if (isset($item['branch_id'])) {
                                        $accessBranchReportDetail[] = [
                                            'branch_id' => $item['branch_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }

                            if (!empty($detailPayload[4]['access_application'])) {

                                foreach ($detailPayload[4]['access_application'] as $item) {
                                    if (isset($item['webstite_access_id'])) {
                                        $accessApplicationDetail[] = [
                                            'webstite_access_id' => $item['webstite_access_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }

                            if (!empty($detailPayload[5]['access_file_principal'])) {

                                foreach ($detailPayload[5]['access_file_principal'] as $item) {
                                    if (isset($item['principal_file_access_id'])) {
                                        $accessFilePrincipalDetail[] = [
                                            'principal_file_access_id' => $item['principal_file_access_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }

                            if (!empty($detailPayload[6]['access_file_principal_group'])) {

                                foreach ($detailPayload[6]['access_file_principal_group'] as $item) {
                                    if (isset($item['principal_group_access_file_id'])) {
                                        $accessFilePrincipalGroupDetail[] = [
                                            'principal_group_access_file_id' => $item['principal_group_access_file_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }

                            if (!empty($detailPayload[7]['access_webbooking_principal_group'])) {

                                foreach ($detailPayload[7]['access_webbooking_principal_group'] as $item) {
                                    if (isset($item['principal_group_web_access_id'])) {
                                        $accessWebbookingPrincipalGroupDetail[] = [
                                            'principal_group_web_access_id' => $item['principal_group_web_access_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }

                            if (!empty($detailPayload[8]['access_doc_dist'])) {

                                foreach ($detailPayload[8]['access_doc_dist'] as $item) {
                                    if (isset($item['doc_access_id'])) {
                                        $accessDocDistDetail[] = [
                                            'doc_access_id' => $item['doc_access_id'],
                                            'remark' => $item['remark'],
                                        ];
                                    }
                                }
                            }


                            $detailBranchArray = [
                                'user_branch' => $accessBranchDetail,
                                'user_branch_report' => $accessBranchReportDetail,
                                'access_application' => $accessApplicationDetail,
                                'access_file_principal' => $accessFilePrincipalDetail,
                                'access_file_principal_group' => $accessFilePrincipalGroupDetail,
                                'access_webbooking_principal_group' => $accessWebbookingPrincipalGroupDetail,
                                'access_doc_dist' => $accessDocDistDetail,
                            ];

                            $objPayload['detail'] = [$detailBranchArray];
                        } else if ($action == 'addUserTeam') {
                            $details = [];
                            $count = 0;
                            if ($detailPayload != '' && $detailPayload != []) {
                                foreach ($detailPayload[0]['users'] as $item) {
                                    $details[] = [
                                        'user_id' => $item['user_id'],
                                        'remark' => $item['remark'] ?? '-',
                                        'ref_id' => $UserTeamDetailIds[$count]
                                    ];
                                    $count += 1;
                                }
                                $detailUserArray = [
                                    'users' => $details,
                                ];
                                $objPayload['detail'] = [$detailUserArray];
                            }
                        } else if ($action == 'addQuotation') {
                            $detailCargo = [];
                            $detailBuy = [];
                            $detailSell = [];

                            if ($detailPayload != '') {
                                $count = 0;
                                foreach ($detailPayload[0]['cargo_details'] as $item) {
                                    $detailCargo[] = [
                                        'package_length' => $item['package_length'] ?? 0,
                                        'package_width' => $item['package_width'] ?? 0,
                                        'package_height' => $item['package_height'] ?? 0,
                                        'package_weight' => $item['package_weight'] ?? 0,
                                        'volume' => $item['volume'] ?? 0,
                                        'gross_weight' => $item['gross_weight'] ?? 0,
                                        'quantity' => $item['quantity'] ?? 0,
                                        'total' => $item['total'] ?? 0,
                                        'remark' => $item['remark'] ?? '-',
                                        'container_type_id' => $item['container_type_id'] ?? null,
                                        'container_size_id' => $item['container_size_id'] ?? null,
                                        'ref_id' => $rfqCargoDetailIds[$count]
                                    ];
                                    $count += 1;
                                }
                                $count = 0;
                                foreach ($detailPayload[1]['service_buy'] as $item) {
                                    $detailBuy[] = [
                                        'service_desc' => $item['service_desc'] ?? '-',
                                        'service_price_desc' => $item['service_price_desc'] ?? '-',
                                        'supplier_service_id' => $item['supplier_service_id'] ?? 0,
                                        'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                        'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                        'remark' => $item['remark'] ?? '-',
                                        'charge_segment' => $item['charge_segment'] ?? '-',
                                        'service_category' => $item['service_category'] ?? 'BUY',
                                        'service_group_id' => $item['service_group_id'] ?? null,
                                        'service_price_id' => $item['service_price_id'] ?? null,
                                        'currency_id' => $item['currency_id'] ?? null,
                                        'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                        'service_id' => $item['service_id'] ?? null,
                                        'principal_id' => $item['principal_id'] ?? null,
                                        'base_price' => $item['base_price'] ?? '',
                                        'ref_id' => $rfqServiceBuyDetailIds[$count]
                                    ];
                                    $count += 1;
                                }
                                $count = 0;
                                foreach ($detailPayload[2]['service_sell'] as $item) {
                                    $detailSell[] = [
                                        'service_desc' => $item['service_desc'] ?? '-',
                                        'service_price_desc' => $item['service_price_desc'] ?? '-',
                                        'supplier_service_id' => $item['supplier_service_id'] ?? 0,
                                        'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                        'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                        'remark' => $item['remark'] ?? '-',
                                        'charge_segment' => $item['charge_segment'] ?? '-',
                                        'service_category' => $item['service_category'] ?? 'SELL',
                                        'service_id' => $item['service_id'] ?? null,
                                        'service_group_id' => $item['service_group_id'] ?? null,
                                        'service_price_id' => $item['service_price_id'] ?? null,
                                        'principal_id' => $item['principal_id'] ?? null,
                                        'currency_id' => $item['currency_id'] ?? null,
                                        'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                                        'qty' => $item['qty'] ?? 0,
                                        'sales_price' => $item['sales_price'] ?? 0,
                                        'total_amount' => $item['total_amount'] ?? 0,
                                        'ref_id' => $rfqServiceSellDetailIds[$count]
                                    ];
                                    $count += 1;
                                }

                                $detailArray = [
                                    'cargo_details' => $detailCargo,
                                    'service_buy' => $detailBuy,
                                    'service_sell' => $detailSell,
                                ];

                                $objPayload['detail'] = [$detailArray];
                            }
                        } else if ($action == 'addPrincipal') {
                            $detailCommodities = [];
                            $detailPics = [];
                            $detailCategories = [];
                            $detailAddresses = [];

                            if ($detailPayload != '') {
                                $count = 0;
                                foreach ($detailPayload[0]['principal_commodity'] as $item) {
                                    $formattedCode = FormattingCodeHelper::formatCode('principal_commodity_category', "", [], [], [], null);

                                    $detailCommodities[] = [
                                        'name' => $item['name'] ?? '-',
                                        'imo' => $item['imo'] ?? '-',
                                        'un' => $item['un'] ?? '-',
                                        'pck_grp' => $item['pck_grp'] ?? '-',
                                        'fi_pt' => $item['fi_pt'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'code' =>  $formattedCode,
                                        'ref_id' => $principalCommoditiesDetailIds[$count]
                                    ];
                                    $count += 1;
                                }

                                $count = 0;
                                foreach ($detailPayload[1]['principal_pic'] as $item) {
                                    $detailPics[] = [
                                        'name' => $item['name'] ?? '-',
                                        'phone' => $item['phone'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'ref_id' => $principalPicsIds[$count]
                                    ];
                                    $count += 1;
                                }

                                $count = 0;
                                foreach ($detailPayload[2]['principal_category'] as $item) {
                                    $detailCategories[] = [
                                        'principal_category_id' => $item['principal_category_id'],
                                        'remark' => $item['remark'] ?? '-',
                                        'ref_id' => $principalCategoriesIds[$count]
                                    ];
                                    $count += 1;
                                }

                                $count = 0;
                                foreach ($detailPayload[4]['address'] as $item) {
                                    $detailAddresses[] = [
                                        'address' => $item['address']  ?? '-',
                                        'pic' => $item['pic']  ?? '-',
                                        'phone' => $item['phone']  ?? '-',
                                        'email' => $item['email']  ?? '-',
                                        'contact' => $item['contact'] ?? '-',
                                        'remark' => $item['remark'] ?? '-',
                                        'note' => $item['note'] ?? '-',
                                        'is_visible' => $item['is_visible'],
                                        'district_id' => $item['district_id'],
                                        'ref_id' => $principalAddressesIds[$count]
                                    ];
                                    $count += 1;
                                }


                                $detailArray = [
                                    'principal_commodity' => $detailCommodities,
                                    'principal_pic' => $detailPics,
                                    'principal_category' => $detailCategories,
                                    'address' => $detailAddresses,
                                ];

                                $objPayload['detail'] = [$detailArray];
                            }
                        }

                        try {
                            // TODO: di response sini kasih rollback jika gagal
                            // TODO: coba : paksa eror di sync nya

                            $response = Http::post($baseSyncUrl . 'toLNJIS2/master/create/' . $model, $objPayload);
                            if ($response) {
                                $timestamp = date('Y-m-d H:i:s');
                                $logEntry = "$timestamp - $response\n";
                                File::append(storage_path('logs/laravel.log'), $logEntry);
                            }
                        } catch (\Exception $e) {
                            $timestamp = date('Y-m-d H:i:s');
                            $errorMessage = $e->getMessage();
                            $logEntry = "$timestamp - $errorMessage\n";
                            File::append(storage_path('logs/error.log'), $logEntry);
                            return false;
                        }
                    }
                }

                return $data;
            });
            DB::commit();

            return $this->sendResponse(
                true,
                Response::HTTP_OK,
                $result
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendResponse(
                false,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e
            );
        }
    }

    /**
     * Display the specified resource.
     */

    public function index(Request $request)
    {

        $actionsToModel = $this->globalActionController->getActionsToModel();

        // TODO: move to global variable
        $additionalActionsToModel = [
            'withCities' => 'cities',
            'withLoadingPort' => 'loadingPort',
            'withService' => 'service',
        ];

        // get action in request
        $action = $request->action;

        $additionalAction = $request->additional_action;

        // get request body
        $requestBody = $request->requestData;

        // get column head for grid
        $columnHead = $request->columnHead;

        if (!array_key_exists($action, $actionsToModel)) {
            return $this->sendResponse(false, Response::HTTP_NOT_FOUND, 'Action Not Found!');
        }

        $arrayAdditionalActionModel = [];

        if (isset($additionalAction)) {
            foreach ($additionalAction as $value) {
                if ($value) {
                    if (!array_key_exists($value, $additionalActionsToModel)) {
                        return $this->sendResponse(false, Response::HTTP_NOT_FOUND, 'Additional Action Not Found!');
                    }
                    //Add Each Model to Array
                    if (isset($additionalActionsToModel[$value])) {
                        $arrayAdditionalActionModel[] = $additionalActionsToModel[$value];
                    }
                }
            }
        }

        if (str_contains($action, 'Enum')) {
            return $this->getEnum($actionsToModel[$action]);
        }

        $filters = $request['filters'];
        $query = $this->modelName($actionsToModel[$action])::query();
        $countData = 0;

        // Get Table Name
        $modelClass = $this->modelName($actionsToModel[$action]);
        $modelInstance = new $modelClass;
        $tableName = $modelInstance->getTable();

        //////////////////////// FILTER ///////////////////////////
        // get Data by ID
        if (isset($filters['id'])) {
            $table = $tableName . ".id";
            $query->where($table, '=', $filters['id']);
        }

        if (isset($request['filters']['custom_filters'])) {
            $searchArray = $request['filters']['custom_filters'];

            $query->where(function ($query) use ($searchArray) {
                foreach ($searchArray as $filter) {
                    if ($filter['term'] == 'like') {
                        $searchTerm = '%' . $filter['query'] . '%';

                        // Split the search query into individual words
                        $words = explode(' ', $filter['query']);

                        // Reverse the order of the words
                        $reversedWords = array_reverse($words);

                        // Join the reversed words to create a reversed search term
                        $reversedSearchTerm = '%' . implode(' ', $reversedWords) . '%';

                        // Use both original and reversed search terms in the query
                        $query->where(function ($query) use ($filter, $searchTerm, $reversedSearchTerm) {
                            $query->where($filter['key'], 'ilike', $searchTerm)
                                ->orWhere($filter['key'], 'ilike', $reversedSearchTerm);
                        });
                    } else if ($filter['term'] == 'equal') {
                        $query->where($filter['key'], '=', $filter['query']);
                    } else if ($filter['term'] == 'not equal') {
                        $query->where($filter['key'], '!=', $filter['query']);
                    } else if ($filter['term'] == 'in') {
                        // Check if query is an array
                        if (is_array($filter['query'])) {
                            $query->orWhereIn($filter['key'], $filter['query']);
                        }
                    }
                }
            });

            // Check for additional search criteria
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
                        } else if ($search['term'] == 'equal') {
                            $query->orWhere($search['key'], '=', $search['query']);
                        } else if ($search['term'] == 'not equal') {
                            $query->orWhere($search['key'], '!=', $search['query']);
                        }
                    }
                });
            }
        }


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
                    } else if ($search['term'] == 'equal') {
                        $query->orWhere($search['key'], '=', $search['query']);
                    } else if ($search['term'] == 'not equal') {
                        $query->orWhere($search['key'], '!=', $search['query']);
                    }
                }
            });
        }

        // Filter by date for master
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $table = $tableName . ".created_at";
            $query->whereBetween($table, [$filters['start_date'], $filters['end_date']]);
        }


        // Filter by request date for quotation
        if (isset($filters['start_request_date']) && isset($filters['end_request_date'])) {
            $table = $tableName . ".request_date";
            $query->whereBetween($table, [$filters['start_request_date'], $filters['end_request_date']]);
        }

        // Filter by date for transaction
        if (isset($filters['transaction_start_date']) && isset($filters['transaction_end_date'])) {
            $table = $tableName . ".transaction_date";
            $query->whereBetween($table, [$filters['start_date'], $filters['end_date']]);
        }

        // filter by active status 1 | other
        if (isset($filters['is_active'])) {
            $table = $tableName . ".is_active";
            $query->where($table, '=', $filters['is_active']);
        }

        // Filter for principal data
        if (isset($filters['principal_id'])) {
            $table = $tableName . ".id";
            $query->where($table, '=', $filters['principal_id']);
        }

        // SORT = DESC | ASC
        if (isset($filters['sort'])) {
            if (isset($filters['order_by'])) {
                // $table = $tableName . "." . $filters['order_by'];
                $table = $filters['order_by'];
            } else {
                $table = 'id';
            }
            $query->orderBy($table ?? 'id', $filters['sort']);
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        if (isset($filters['skip']) && isset($filters['take'])) {
            $query->skip($filters['skip']);
            $query->take($filters['take']);
        }

        // Filter untuk menu Quotation
        if (isset($filters['status'])) {
            if ($filters['status'] == 'not-approved') {
                $value = false;
            } else if ($filters['status'] == 'approved') {
                $value = true;
            }
            $table = $tableName . ".is_approve";
            $query->where($table, '=', $value);
        }
        if (isset($filters['status_close'])) {
            if ($filters['status_close'] == 'open') {
                $value = false;
            } else if ($filters['status_close'] == 'close') {
                $value = true;
            }
            $table = $tableName . ".is_finish_quot";
            $query->where($table, '=', $value);
        }

        // Filter untuk menu pre order
        if (isset($filters['status_job'])) {
            if ($filters['status_job'] == 'created') {
                $query->where($tableName . '.job_order_id', '!=', null);
            } else if ($filters['status_job'] == 'not created') {
                $query->where($tableName . '.job_order_id', '=', null);
            } else {
                $query->where(function ($query) use ($tableName) {
                    $query->where($tableName . '.job_order_id', '!=', null)
                        ->orWhere($tableName . '.job_order_id', '=', null);
                });
            }
        }

        if (isset($filters['status_preorder'])) {
            if ($filters['status_preorder'] == 'canceled') {
                $query->where($tableName . '.rejected_by', '!=', null);
            } else if ($filters['status_preorder'] == 'not canceled') {
                $query->where($tableName . '.rejected_by', '=', null);
            } else {
                $query->where(function ($query) use ($tableName) {
                    $query->where($tableName . '.rejected_by', '!=', null)
                        ->orWhere($tableName . '.rejected_by', '=', null);
                });
            }
        }

        if (isset($filters['status_flow'])) {
            if ($filters['status_flow'] == 'draft') {
                $query->where($tableName . '.is_draft', '=', true);
            } else if ($filters['status_flow'] == 'request rfq') {
                $query->where($tableName . '.is_request_rfq', '=', true);
            } else if ($filters['status_flow'] == 'submitted') {
                $query->where($tableName . '.is_submit', '=', true);
            } else {
                $query->where(function ($query) use ($tableName) {
                    $query->where($tableName . '.is_draft', '=', true)
                        ->orWhere($tableName . '.is_request_rfq', '=', true)
                        ->orWhere($tableName . '.is_submitted', '=', true)
                        ->orWhere(function ($query) use ($tableName) {
                            $query->where($tableName . '.is_draft', '=', false)
                                ->where($tableName . '.is_request_rfq', '=', false)
                                ->where($tableName . '.is_submitted', '=', false);
                        });
                });
            }
        }

        if (isset($filters['trade'])) {
            if ($filters['trade'] == 'export') {
                $query->where($tableName . '.trade', '=', 'export');
            } else if ($filters['trade'] == 'import') {
                $query->where($tableName . '.trade', '=', 'import');
            } else if ($filters['trade'] == 'domestic') {
                $query->where($tableName . '.trade', '=', 'domestic');
            } else if ($filters['trade'] == 'xbook') {
                $query->where($tableName . '.trade', '=', 'xbook');
            }
        }


        // join created updated deleted by user name
        // dd($tableName);

        // $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', $tableName . '.created_by')
        //     ->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', $tableName . '.updated_by')
        //     ->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', $tableName . '.deleted_by')
        //     ->select(
        //         $tableName . '.*',
        //         'created_by_user.name as created_by',
        //         'updated_by_user.name as updated_by',
        //         'deleted_by_user.name as deleted_by',
        //     )
        //     ->get();

        // $queries = \DB::getQueryLog();
        // dd($queries);

        //////////////////////// END OF FILTER ///////////////////////////

        // get detail table
        if ($arrayAdditionalActionModel) {
            $query->with($arrayAdditionalActionModel);
        }


        // custom action & queries
        if ($action == 'getBranch') {
            $query->leftJoin('ports', 'ports.id', '=', 'branches.port_id');
            $query->leftJoin('employees AS pic', 'pic.id', '=', 'branches.pic_employee_id');
            $query->leftJoin('employees AS tax', 'tax.id', '=', 'branches.tax_employee_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'branches.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'branches.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'branches.deleted_by');
            $query->select(
                'branches.*',
                'ports.name AS port_name',
                'pic.name AS pic_employee_name',
                'tax.name AS tax_employee_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getCity') {
            // $query->leftJoin('countries', 'countries.id', '=', 'cities.country_id');
            // $query->select('cities.*', 'countries.name AS country_name');
            $query->where('ports.is_city', '=', true);
        } else if ($action == 'getPort') {
            // $query->leftJoin('ports as city_port', 'city_port.id', '=', 'ports.city_id');

            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'ports.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'ports.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'ports.deleted_by');
            $query->select(
                'ports.*',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
            $query->where('ports.is_city', '=', false);
        } else if ($action == 'getCarrier') {
            $query->leftJoin('countries', 'countries.id', '=', 'carriers.country_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'carriers.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'carriers.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'carriers.deleted_by');
            $query->select(
                'carriers.*',
                'countries.name AS country_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getNotification') {
            if (isset($filters['user_group_id'])) {
                $query->where('user_group_id', '=', $filters['user_group_id']);
            }
            $countData = $query->count();
        } else if ($action == 'getLockedParty') {
            $query->leftJoin('principal_blacklists AS b', 'b.principal_id', '=', 'principals.id');
            $query->select(
                'principals.*',
                'b.id AS principal_blacklist_id',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
                DB::raw('CASE WHEN b.principal_id IS NOT NULL THEN TRUE ELSE FALSE END AS is_locked')
            );
        } else if ($action == 'getQuotation') {
            $query->leftJoin('principals', 'principals.id', '=', 'rfqs.principal_id');
            $query->leftJoin('employees', 'employees.id', '=', 'rfqs.employee_id');
            $query->leftJoin('container_types', 'container_types.id', '=', 'rfqs.container_type_id');
            $query->leftJoin('principal_commodities', 'principal_commodities.id', '=', 'rfqs.commodity');
            $query->leftJoin('service_terms as incoterm', 'incoterm.id', '=', 'rfqs.incoterm_service_term_id');
            $query->leftJoin('service_terms as term_of_service', 'term_of_service.id', '=', 'rfqs.payment_term_tos_id');
            $query->leftJoin('ports as loading', 'loading.id', '=', 'rfqs.loading_port_id');
            $query->leftJoin('ports as discharge', 'discharge.id', '=', 'rfqs.discharge_port_id');
            $query->leftJoin('ports as pickup', 'pickup.id', '=', 'rfqs.from_port_id');
            $query->leftJoin('ports as delivery', 'delivery.id', '=', 'rfqs.to_port_id');
            $query->leftJoin('rfqs as parent_rfq', 'parent_rfq.id', '=', 'rfqs.parent_rfq_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'rfqs.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'rfqs.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'rfqs.deleted_by');
            $query->select(
                'rfqs.*',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
                'principals.name AS principal_name',
                'employees.name AS employee_name',
                'container_types.name AS container_type_name',
                'principal_commodities.name AS commodity_name',
                'incoterm.name AS incoterm_service_term_name',
                'term_of_service.name AS payment_term_tos_name',
                'loading.name AS loading_port_name',
                'discharge.name AS discharge_port_name',
                'pickup.name AS pickup_from_port_name',
                'delivery.name AS delivery_to_port_name',
                'parent_rfq.code AS parent_rfq_code',
                DB::raw("CASE
                            WHEN NOT rfqs.is_approve AND NOT rfqs.is_finish_quot THEN 'Waiting for Approval'
                            WHEN rfqs.is_approve AND NOT rfqs.is_finish_quot THEN 'Approved'
                            ELSE 'Finished'
                        END AS status"),
                DB::raw("CONCAT(
                            SUBSTR(CONCAT(
                                CASE WHEN rfqs.is_customs_clearance THEN '/ Customs Clereance ' ELSE '' END,
                                CASE WHEN rfqs.is_freight THEN '/ Freight ' ELSE '' END,
                                CASE WHEN rfqs.is_shipping_agency THEN '/ Shipping Agency ' ELSE '' END,
                                CASE WHEN rfqs.is_other THEN '/ Other ' ELSE '' END
                            ),3),
                            CASE
                                WHEN rfqs.trade = 'domestic' THEN '- Domestic '
                                WHEN rfqs.trade = 'xbook' THEN '- Xbook '
                                WHEN rfqs.trade = 'export' THEN '- Export '
                                WHEN rfqs.trade = 'import' THEN '- Import '
                                ELSE ''
                            END
                            ||
                            CASE
                                WHEN rfqs.type_of_shipment = 'break_bulk' THEN '- Break Bulk '
                                WHEN rfqs.type_of_shipment = 'fcl' THEN '- FCL '
                                WHEN rfqs.type_of_shipment = 'lcl' THEN '- LCL '
                                WHEN rfqs.type_of_shipment = 'ftl' THEN '- FTL '
                                WHEN rfqs.type_of_shipment = 'ltl' THEN '- LTL '
                                WHEN rfqs.type_of_shipment = 'containerized' THEN '- Containerized '
                                ELSE ''
                            END
                            ||
                            CASE
                                WHEN rfqs.shipping_mode = 'sea' THEN '- Sea '
                                WHEN rfqs.shipping_mode = 'air' THEN '- Air '
                                WHEN rfqs.shipping_mode = 'rail' THEN '- Rail '
                                WHEN rfqs.shipping_mode = 'road' THEN '- Road '
                                ELSE ''
                            END
                        ) AS request_description"),
            );
        } else if ($action == 'getContainer') {
            $query->leftJoin('container_types', 'container_types.id', '=', 'containers.container_type_id');
            $query->leftJoin('container_sizes', 'container_sizes.id', '=', 'containers.container_size_id');
            $query->leftJoin('container_agencies', 'container_agencies.id', '=', 'containers.container_agency_id');
            $query->leftJoin('principals AS shipper', 'shipper.id', '=', 'containers.shipper_id');
            $query->leftJoin('principals AS consignee', 'consignee.id', '=', 'containers.consignee_id');
            $query->leftJoin('principals AS sender', 'sender.id', '=', 'containers.sender_id');
            $query->leftJoin('principals AS receiver', 'receiver.id', '=', 'containers.receiver_id');
            $query->leftJoin('carriers AS cargo', 'cargo.id', '=', 'containers.cargo_id');
            $query->leftJoin('carriers AS vessel', 'vessel.id', '=', 'containers.vessel_id');
            $query->leftJoin('ports AS loading', 'loading.id', '=', 'containers.loading_port_id');
            $query->leftJoin('ports AS discharge', 'discharge.id', '=', 'containers.discharge_port_id');
            $query->leftJoin('currencies', 'currencies.id', '=', 'containers.currency_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'containers.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'containers.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'containers.deleted_by');
            $query->select(
                'containers.*',
                'container_types.name AS container_type_name',
                'container_sizes.name AS container_size_name',
                'container_agencies.name AS container_agency_name',
                'shipper.name AS shipper_name',
                'consignee.name AS consignee_name',
                'sender.name AS sender_name',
                'receiver.name AS receiver_name',
                'cargo.name AS cargo_name',
                'vessel.id AS vessel_name',
                'loading.name AS loading_port_name',
                'discharge.name AS discharge_port_name',
                'currencies.name AS currency_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getDocumentSetting') {
            $query->leftJoin('branches', 'branches.id', '=', 'document_settings.branch_id');
            $query->leftJoin('users as admin_from', 'admin_from.id', '=', 'document_settings.admin_from_id');
            $query->leftJoin('users as admin_to', 'admin_to.id', '=', 'document_settings.admin_to_id');
            $query->leftJoin('document_distributions', 'document_distributions.id', '=', 'document_settings.document_distribution_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'document_settings.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'document_settings.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'document_settings.deleted_by');
            $query->select(
                'document_settings.*',
                'branches.name AS branch_name',
                'admin_from.username AS admin_from_name',
                'admin_to.username AS admin_to_name',
                'document_distributions.name AS document_distribution_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
                DB::raw("TO_CHAR(document_settings.created_at, 'DD Mon YYYY HH24:MI:SS') AS formatted_created_at")
            );
        } else if ($action == 'getPortInterchange') {
            $query->leftJoin('ports', 'ports.id', '=', 'port_interchanges.port_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'port_interchanges.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'port_interchanges.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'port_interchanges.deleted_by');
            $query->select(
                'port_interchanges.*',
                'ports.name AS port_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getPrincipalCategory') {
            $query->leftJoin('service_categories', 'service_categories.id', '=', 'principal_categories.service_category_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'principal_categories.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'principal_categories.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'principal_categories.deleted_by');
            $query->select(
                'principal_categories.*',
                'service_categories.name AS service_category_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getServicePrice') {
            $query->leftJoin('ports AS loading', 'loading.id', '=', 'service_prices.loading_port_id');
            $query->leftJoin('ports AS discharge', 'discharge.id', '=', 'service_prices.discharge_port_id');
            $query->leftJoin('principal_commodities', 'principal_commodities.id', '=', 'service_prices.commodity_id');
            $query->leftJoin('services', 'services.id', '=', 'service_prices.service_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'service_prices.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'service_prices.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'service_prices.deleted_by');
            $query->leftJoin('service_groups', 'service_groups.id', '=', 'services.service_group_id_3');
            $query->select(
                'service_prices.*',
                'loading.name AS loading_port_name',
                'discharge.name AS discharge_port_name',
                'principal_commodities.name AS commodity_name',
                'services.name AS service_name',
                'service_groups.name AS service_group_name',
                'service_groups.id AS service_group_id',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getServiceTag') {
            $query->leftJoin('principals', 'principals.id', '=', 'service_tags.principal_id');
            $query->leftJoin('service_groups', 'service_groups.id', '=', 'service_tags.service_group_id');
            $query->leftJoin('services', 'services.id', '=', 'service_tags.service_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'service_tags.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'service_tags.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'service_tags.deleted_by');
            $query->select(
                'service_tags.*',
                'principals.name AS principal_name',
                'service_groups.name AS service_group_name',
                'services.name AS service_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getPrincipalBankAccount') {
            $query->leftJoin('principals', 'principals.id', '=', 'principal_bank_accounts.principal_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'principal_bank_accounts.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'principal_bank_accounts.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'principal_bank_accounts.deleted_by');
            $query->select(
                'principal_bank_accounts.*',
                'principals.name AS principal_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getPrincipal') {
            $query->leftJoin('principal_groups', 'principal_groups.id', '=', 'principals.principal_group_id');
            $query->leftJoin('ports', 'ports.id', '=', 'principals.city_id');
            $query->leftJoin('employees', 'employees.id', '=', 'principals.salesman');
            $query->leftJoin('principal_bank_accounts AS bank', 'bank.principal_id', '=', 'principals.id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'principals.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'principals.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'principals.deleted_by');
            $query->select(
                'principals.*',
                'principal_groups.name AS principal_group_name',
                'ports.name AS port_name',
                'employees.name AS salesman_name',
                'bank.name AS bank_name',
                'bank.number AS bank_no',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getUserTeam') {
            $query->leftJoin('menus', 'menus.id', '=', 'user_teams.menu_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'user_teams.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'user_teams.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'user_teams.deleted_by');
            $query->select(
                'user_teams.*',
                'menus.name AS menu_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getEmployee') {
            $query->leftJoin('cities as address_city', 'address_city.id', '=', 'employees.address_city_id');
            $query->leftJoin('cities as born_city', 'born_city.id', '=', 'employees.born_city_id');
            $query->leftJoin('branches', 'branches.id', '=', 'employees.branch_id');
            $query->leftJoin('cities as domicile_city', 'domicile_city.id', '=', 'employees.domicile_city_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'employees.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'employees.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'employees.deleted_by');
            $query->select(
                'employees.*',
                'branches.name AS branch_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getService') {
            $query->leftJoin('service_budgets as service_budgets', 'service_budgets.id', '=', 'services.service_budget_id');
            $query->leftJoin('service_groups as service_group_invoice', 'service_group_invoice.id', '=', 'services.service_group_id');
            $query->leftJoin('service_groups as service_groups_1', 'service_groups_1.id', '=', 'services.service_group_id_1');
            $query->leftJoin('service_groups as service_groups_2', 'service_groups_2.id', '=', 'services.service_group_id_2');
            $query->leftJoin('service_groups as service_groups_3', 'service_groups_3.id', '=', 'services.service_group_id_3');
            $query->leftJoin('service_groups as service_groups_export', 'service_groups_export.id', '=', 'services.service_group_export_id');

            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'services.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'services.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'services.deleted_by');
            $query->select(
                'services.*',
                'service_budgets.name AS service_budget_name',
                'service_group_invoice.name AS service_group_name',
                'service_groups_1.name AS service_group_1_name',
                'service_groups_2.name AS service_group_2_name',
                'service_groups_3.name AS service_group_3_name',
                'service_groups_export.name AS service_group_export_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } else if ($action == 'getPreOrder') {
            $query->leftJoin('principals', 'principals.id', '=', 'pre_orders.principal_shipper_id');
            $query->leftJoin('rfqs AS job_emkl', 'job_emkl.id', '=', 'pre_orders.rfq_id');
            $query->leftJoin('rfqs AS job_forwarding', 'job_forwarding.id', '=', 'pre_orders.rfq_child_id');
            $query->leftJoin('principals AS shipper', 'shipper.id', '=', 'pre_orders.principal_shipper_id');
            $query->leftJoin('principals AS consignee', 'consignee.id', '=', 'pre_orders.principal_consignee_id');
            $query->leftJoin('ports AS pol', 'pol.id', '=', 'pre_orders.port_loading_id');
            $query->leftJoin('ports AS pod', 'pod.id', '=', 'pre_orders.port_discharge_id');
            $query->leftJoin('ports AS terminal', 'terminal.id', '=', 'pre_orders.port_terminal_id');
            $query->leftJoin('ports AS warehouse', 'warehouse.id', '=', 'pre_orders.port_warehouse_id');
            $query->leftJoin('service_terms', 'service_terms.id', '=', 'pre_orders.service_term_id');
            $query->leftJoin('commodity_categories AS commodity', 'commodity.id', '=', 'pre_orders.commodity_id');
            $query->leftJoin('users', 'users.id', '=', 'pre_orders.created_by');
            $query->select(
                'pre_orders.id',
                'pre_orders.code AS booking_code',
                'job_emkl.code AS job_emkl',
                'job_forwarding.code AS job_forwarding',
                'pre_orders.invoice_number AS invoice',
                'pre_orders.hbl',
                'pre_orders.mbl',
                'pre_orders.bc_id',
                'pre_orders.po_number as po_number',
                'pre_orders.principal_shipper_id AS shipper_id',
                'shipper.name AS shipper_name',
                'pre_orders.principal_consignee_id AS consignee_id',
                'consignee.name AS consignee_name',
                'pol.id AS pol_id',
                'pod.id AS pod_id',
                'pol.name AS pol',
                'pod.name AS pod',
                'terminal.id AS port_terminal_id',
                'terminal.name AS terminal_name',
                'service_terms.id AS service_term_id',
                'service_terms.name AS service_term_name',
                'warehouse.name AS port_warehouse_name',
                'pre_orders.trade',
                'pre_orders.shipping_mode',
                'pre_orders.type',
                'pre_orders.ref',
                'pre_orders.softcopy_send_date AS softcopy',
                'pre_orders.original_bl_rcv_date AS original_bl',
                'pre_orders.original_ipl_date AS original_ipl',
                'pre_orders.original_coo_rcv_date AS original_coo',
                'job_emkl.valid_until',
                'job_emkl.request_date',
                'pre_orders.commodity',
                'commodity.name AS commodity_desc',
                DB::raw("CASE WHEN pre_orders.is_approve = true THEN 'Approved' ELSE 'Not Approved' END AS status"),
                'pre_orders.admin_onhand_rfq_id',
                'pre_orders.admin_onhand_submit_id',
                'pre_orders.is_active',
                'pre_orders.created_by',
                'pre_orders.updated_by',
                'pre_orders.deleted_by',
                'pre_orders.created_at',
                'pre_orders.updated_at',
                'pre_orders.deleted_at',
                'pre_orders.is_customs_clearance',
                'pre_orders.is_freight',
                DB::raw("CASE WHEN pre_orders.ref_id != null THEN 'true' ELSE 'false' END AS is_external"),
                DB::raw("CASE WHEN CURRENT_DATE > job_emkl.valid_until THEN 'false' ELSE 'true' END AS is_valid"),
                DB::raw("CASE WHEN pre_orders.admin_onhand_submit_id != null THEN 'Submitted' ELSE 'Draft' END AS onhance_flow"),
                'users.name AS created_user_name',
            );
        } else if ($action == 'getUserBranchGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getUserBranchGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getUserCompanyGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getUserCompanyGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getUserGroupUserGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getUserGroupUserGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        }
        // Access Users
        else if ($action == 'getBranchAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getBranchAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getBranchReportAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getBranchReportAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getWebsiteAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getWebsiteAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getPrincipalFileAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getPrincipalFileAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getPrincipalGroupFileAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getPrincipalGroupFileAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getPrincipalGroupAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getPrincipalGroupAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        } else if ($action == 'getDocumentDistributionAccessGrid') {
            // Must be filtered by user id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userGridService->getDocumentDistributionAccessGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Id is required!');
            }
        }
        // Principal Grid
        else if ($action == 'getPrincipalCommodityGrid') {
            // must be filtered by principal id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->principalDataGridService->getPrincipalComodityGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Principal Id is required!');
            }
        } else if ($action == 'getPrincipalPicGrid') {
            // must be filtered by principal id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->principalDataGridService->getPrincipalPicGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Principal Id is required!');
            }
        } else if ($action == 'getPrincipalCategoryGrid') {
            // must be filtered by principal id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->principalDataGridService->getPrincipalCategoryGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Principal Id is required!');
            }
        } else if ($action == 'getServiceFromSupplierGrid') {
            // must be filtered by principal id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->principalDataGridService->getServiceFromSupplierGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Principal Id is required!');
            }
        } else if ($action == 'getPrincipalAddressGrid') {
            // must be filtered by principal id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->principalDataGridService->getPrincipalAddressGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Principal Id is required!');
            }
        } else if ($action == 'getUserTeamUserGrid') {
            // must be filtered by user team id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->userTeamGridService->getUserGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by User Team Id is required!');
            }
        } else if ($action == 'getUser') {
            $query->leftJoin('employees AS employee', 'employee.id', '=', 'users.employee_id');
            $query->leftJoin('branches AS branch', 'branch.id', '=', 'users.branch_id');
            $query->leftJoin('companies AS company', 'company.id', '=', 'users.company_id');
            $query->leftJoin('user_groups AS userGroup', 'userGroup.id', '=', 'users.user_group_id');
            $query->select('users.*', 'employee.name AS employee_name', 'company.name AS company_name', 'branch.name AS branch_name', 'userGroup.name AS user_group_name');
        } else if ($action == 'getRfqCargoGrid') {
            // must be filtered by Rfq id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->quotationGridService->getCargoGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Rfq Id is required!');
            }
        } else if ($action == 'getRfqServiceBuyGrid') {
            // must be filtered by Rfq id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->quotationGridService->getServiceBuyGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Rfq Id is required!');
            }
        } else if ($action == 'getRfqServiceSellGrid') {
            // must be filtered by Rfq id
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->quotationGridService->getServiceSellGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Rfq Id is required!');
            }
        } else if ($action == 'getDetailPartyGrid') {
            // Must be filtered by preOrder id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->preOrderGridService->getDetailPartyGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by PreOrder Id is required!');
            }
        } else if ($action == 'getDetailPackingGrid') {
            // Must be filtered by preOrder id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->preOrderGridService->getDetailPackingGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by PreOrder Id is required!');
            }
        } else if ($action == 'getDetailItemShipmentGrid') {
            // Must be filtered by preOrder id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->preOrderGridService->getDetailItemShipmentGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by PreOrder Id is required!');
            }
        } else if ($action == 'getDetailContainerGrid') {
            // Must be filtered by preOrder id.
            if (isset($filters['id'])) {
                if (isset($columnHead)) {
                    $result = $this->preOrderGridService->getDetailPreOrderContainerGrid($filters['id'], $columnHead);
                    return $result;
                } else {
                    return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Column head is required!');
                }
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by PreOrder Id is required!');
            }
        } else if ($action == 'getPrincipalGrid') {
            // id = principal_group_id
            if (isset($filters['id'])) {

                $data = Principal::where('principal_group_id', $filters['id'])
                    ->select('id as principal_id', 'name as principal_name')
                    ->get();

                $countData = $data->count();

                $result = array('rows' => $data, 'page' => 1, 'records' => $countData, 'total' => $countData);

                return $this->sendResponse(
                    true,
                    Response::HTTP_OK,
                    $result
                );
            } else {
                return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, 'Filter by Principal group Id is required!');
            }
        } else {

            // JOIN nama users di created_by

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


        // Add User Branch in Query || hanya select jika table memiliki kolom branch_id
        if (Schema::hasColumn($tableName, 'branch_id')) {
            if ($tableName != 'employees' && $tableName != 'document_settings' && $tableName != 'users') {
                $userId = auth()->id();
                $userBranchIds = [];
                $userInBranchIds = [];

                $data = DB::table('user_branches as a')
                    ->where('a.user_id', $userId)
                    ->selectRaw('a.branch_id')
                    ->get();
                foreach ($data as $id) {
                    $userBranchIds[] = $id->branch_id;
                }
                // dd($userBranchIds);
                // $temp = DB::table('user_branches as a')
                //     ->whereIn('branch_id', $userBranchIds)
                //     ->select('a.user_id')
                //     ->distinct() // Make sure to retrieve distinct user IDs
                //     ->get();
                // foreach ($temp as $id) {
                //     $userInBranchIds[] = $id->user_id;
                // }

                if (!empty($userBranchIds)) {
                    $table = $tableName . ".branch_id";
                    $query->whereIn($table, $userBranchIds);
                }
            }
        }

        // count data for datatable when there is a filter or not
        $countDataQuery = function ($query) {
            return $query->toBase()->getCountForPagination();
        };

        $countData = $countDataQuery($query);

        $data = $query->get();

        if (isset($requestBody['draw'])) {
            $draw = $requestBody['draw'];
        }

        $result = array('draw' => $draw ?? '', 'recordsTotal' => $countData, 'recordsFiltered' => $countData, 'total_rows' => $countData, 'data' => $data);

        return $this->sendResponse(
            true,
            Response::HTTP_OK,
            $result
        );
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $actionsToModel = $this->globalActionController->getActionsToModel();
        // get action in request
        $action = $request->action;
        // get request body
        $requestBody = $request->requestData;
        // set user
        $requestBody['updated_by'] = auth()->id();

        $isFromLNJ2 = $requestBody['is_lnj2'] ?? false;
        $containRefId = $requestBody['ref_id_true'] ?? false;

        // Untuk simpan id yang dicreate ketika masuk endpoint update
        $newUserTeamDetailIds = [];
        // Grid Principas di principal group
        $newPrincipalIds = [];

        // Grid principal Data
        $newPrincipalCommodityIds = [];
        $newPicIds = [];
        $newCategoryIds = [];
        $newAddressIds = [];

        // Grid Quotation (Untuk simpan id yang dicreate ketika masuk endpoint update)
        $newCargoDetailIds = [];
        $newBuyDetailIds = [];
        $newSellDetailIds = [];


        // if value code sent from front end null then unset
        if (array_key_exists('code', $requestBody) && $requestBody['code'] === null) {
            unset($requestBody['code']);
        }
        // if action updateUser then remove confirm password key
        if ($action == 'updateUser') {
            if (array_key_exists('confirm_password', $requestBody)) {
                unset($requestBody['confirm_password']);
            }
        } else if ($action == 'updateQuotation') {
            $requestBody['request_date'] = $requestBody['request_date'] ?? date("Y-m-d");
            $requestBody['commodity'] = $requestBody['commodity'] ?? 0;
            $requestBody['commodity_desc'] = $requestBody['commodity_desc'] ?? '-';
            $requestBody['term_of_payment'] = $requestBody['term_of_payment'] ?? 0;
            $requestBody['quantity'] = $requestBody['quantity'] ?? '-';
            $requestBody['weight'] = $requestBody['weight'] ?? '-';
            $requestBody['measurement'] = $requestBody['measurement'] ?? '-';
            $requestBody['imoun'] = $requestBody['imoun'] ?? '-';
            $requestBody['free_time'] = $requestBody['free_time'] ?? 0;
            if (isset($requestBody['detail'][3]['notes'])) {
                foreach ($requestBody['detail'][3]['notes'] as $item) {
                    $requestBody['main_notes'] = $item;
                }
            } else {
                $requestBody['main_notes'] = $requestBody['main_notes'] ?? '-';
            }
        } else if ($action == 'updateNote') {
            $match = Note::where('content', 'ILIKE', $requestBody['content'])->whereNull('deleted_at')->where('id', '!=', $id)->exists();
            if ($match) {
                return $this->sendResponse(false, Response::HTTP_INTERNAL_SERVER_ERROR, 'Content input are same, please input other content.');
            }
        } else if ($action == 'updateContainer') {
            //TODO: remove kalau sudah ada kejelasan mengenai upload file
            unset($requestBody['file']);
            unset($requestBody['file_old']);
        } else if ($action == 'updateUserGroup') {
            if (array_key_exists('group_user_template_id', $requestBody)) {
                unset($requestBody['group_user_template_id']);
            }
        }

        // Menghapus atribut yang mengandung "_submit"
        // TODO: handle _submit di FE karena ada field yg namanya perlu _submit belakangnya
        if ($action != 'updatePreOrder') {
            foreach ($requestBody as $key => $value) {
                if (is_string($value) && Str::endsWith($key, '_submit')) {
                    unset($requestBody[$key]);
                }
            }
        }

        if (array_key_exists('date_submit', $requestBody)) {
            unset($requestBody['date_submit']);
        }

        if (array_key_exists('_method', $requestBody)) {
            unset($requestBody['_method']);
        }
        if (array_key_exists('_token', $requestBody)) {
            unset($requestBody['_token']);
        }

        if (!array_key_exists($action, $actionsToModel)) {
            return $this->sendResponse(false, Response::HTTP_NOT_FOUND, 'Action Not Found!');
        }

        if (!$isFromLNJ2 || $containRefId) {
            $data = $this->modelName($actionsToModel[$action])::findOrFail($id);
        } else {
            $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->value('ref_id');
        }


        $result =  DB::transaction(function () use ($actionsToModel, $action, $requestBody, $id) {

            $isFromLNJ2 = false;
            $containRefId = false;
            if (isset($requestBody['is_lnj2'])) {
                $isFromLNJ2 = true;
                unset($requestBody['is_lnj2']);
            }

            if (isset($requestBody['ref_id_true'])) {
                $containRefId = true;
                unset($requestBody['ref_id_true']);
            }

            if ($action == 'updateNotification') {
                for ($i = 0; $i < $requestBody['notification_ids']; $i++) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $requestBody['notification_ids'][$i])->update(['is_read' => 1]);
                }
            } else if ($action == 'updateUser') {
                // update grid

                $branchIds = [];
                $userGroupIds = [];
                $companyIds = [];


                $branchDetails = [];
                $branchReportDetails = [];
                $accessApplicationDetails = [];
                $accessPrincipalFileDetails = [];
                $accessPrincipalGroupFileDetails = [];
                $accessPrincipalGroupDetails = [];
                $accessDocumentDistributionDetails = [];


                if (!empty($requestBody['detail'])) {
                    $detailPayload = $requestBody['detail'];


                    if (isset($detailPayload[0]['user_branch'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_branch')
                            ->delete();

                        if (!empty($detailPayload[0]['user_branch'])) {
                            foreach ($requestBody['detail'][0]['user_branch'] as $item) {
                                if (isset($item['branch_id'])) {
                                    $branchIds[] = $item['branch_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['branch_id'],
                                    'relation_type' => 'master_branch',
                                    'relation_table' => 'branches',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_branch')
                                ->delete();
                        }
                    }



                    if (isset($detailPayload[1]['user_branch_report'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_branch_report')
                            ->delete();
                        if (!empty($detailPayload[1]['user_branch_report'])) {

                            foreach ($requestBody['detail'][1]['user_branch_report'] as $item) {
                                if (isset($item['branch_id'])) {
                                    $branchReportIds[] = $item['branch_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['branch_id'],
                                    'relation_type' => 'master_branch_report',
                                    'relation_table' => 'branches',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_branch_report')
                                ->delete();
                        }
                    }


                    if (!empty($detailPayload[2]['user_group'])) {
                        foreach ($requestBody['detail'][2]['user_group'] as $item) {
                            if (isset($item['user_group_id'])) {
                                $userGroupIds[] = $item['user_group_id'];
                            }
                        }
                    }

                    if (!empty($detailPayload[3]['user_company'])) {
                        foreach ($requestBody['detail'][3]['user_company'] as $item) {
                            if (isset($item['company_id'])) {
                                $companyIds[] = $item['company_id'];
                            }
                        }
                    }

                    if (isset($detailPayload[4]['access_application'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_website')
                            ->delete();

                        if (!empty($detailPayload[4]['access_application'])) {

                            foreach ($requestBody['detail'][4]['access_application'] as $item) {
                                if (isset($item['webstite_access_id'])) {
                                    $accessApplicationIds[] = $item['webstite_access_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['webstite_access_id'],
                                    'relation_type' => 'master_website',
                                    'relation_table' => 'websites',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_website')
                                ->delete();
                        }
                    }

                    if (isset($detailPayload[5]['access_file_principal'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_principal_file')
                            ->delete();
                        if (!empty($detailPayload[5]['access_file_principal'])) {
                            foreach ($requestBody['detail'][5]['access_file_principal'] as $item) {


                                if (isset($item['principal_file_access_id'])) {
                                    $accessPrincipalFileIds[] = $item['principal_file_access_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['principal_file_access_id'],
                                    'relation_type' => 'master_principal_file',
                                    'relation_table' => 'principals',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_principal_file')
                                ->delete();
                        }
                    }

                    if (isset($detailPayload[6]['access_file_principal_group'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_principal_group_file')
                            ->delete();
                        if (!empty($detailPayload[6]['access_file_principal_group'])) {


                            foreach ($requestBody['detail'][6]['access_file_principal_group'] as $item) {
                                if (isset($item['principal_group_access_file_id'])) {
                                    $accessPrincipalGroupFileIds[] = $item['principal_group_access_file_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['principal_group_access_file_id'],
                                    'relation_type' => 'master_principal_group_file',
                                    'relation_table' => 'principal_groups',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            // delete first and then insert new value
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_principal_group_file')
                                ->delete();
                        }
                    }

                    if (isset($detailPayload[7]['access_webbooking_principal_group'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_principal_group')
                            ->delete();

                        if (!empty($detailPayload[7]['access_webbooking_principal_group'])) {

                            foreach ($requestBody['detail'][7]['access_webbooking_principal_group'] as $item) {
                                if (isset($item['principal_group_web_access_id'])) {
                                    $accessPrincipalGroupIds[] = $item['principal_group_web_access_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['principal_group_web_access_id'],
                                    'relation_type' => 'master_principal_group',
                                    'relation_table' => 'principal_groups',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_principal_group')
                                ->delete();
                        }
                    }

                    if (isset($detailPayload[8]['access_doc_dist'])) {
                        // delete first and then insert new value
                        AccessMaster::where('user_id', $id)
                            ->where('relation_type', 'master_document_distribution')
                            ->delete();
                        if (!empty($detailPayload[8]['access_doc_dist'])) {


                            foreach ($requestBody['detail'][8]['access_doc_dist'] as $item) {
                                if (isset($item['doc_access_id'])) {
                                    $accessDocumentDistributionIds[] = $item['doc_access_id'];
                                }

                                AccessMaster::create([
                                    'user_id' => $id,
                                    'relation_id' => $item['doc_access_id'],
                                    'relation_type' => 'master_document_distribution',
                                    'relation_table' => 'document_distributions',
                                    'remark' => $item['remark'],
                                ]);
                            }
                        } else {
                            AccessMaster::where('user_id', $id)
                                ->where('relation_type', 'master_document_distribution')
                                ->delete();
                        }
                    }
                }

                if (!$isFromLNJ2 || $containRefId) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();
                } else {
                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->first();
                }

                if ($data) {
                    $data->update($requestBody);

                    // Update payload branch Ids to user_branches

                    if (!$isFromLNJ2) {

                        if (empty($branchIds)) {
                            $data->branches()->detach();
                        } else {
                            $data::where('id', $id)->update([
                                'branch_id' => $branchIds[0],
                            ]);
                            $data->branches()->sync($branchIds);
                        }

                        // Update payload company Ids to user_companies
                        if (empty($companyIds)) {
                            $data->companies()->detach();
                        } else {
                            $data::where('id', $id)->update([
                                'company_id' => $companyIds[0]
                            ]);
                            $data->companies()->sync($companyIds);
                        }

                        // Update payload user group Ids to user_group_users
                        if (empty($userGroupIds)) {
                            $data->user_groups()->detach();
                        } else {
                            $data::where('id', $id)->update([
                                'user_group_id' => $userGroupIds[0]
                            ]);
                            $data->user_groups()->sync($userGroupIds);
                        }
                    } else {
                        if (empty($branchIds)) {
                            $data->branches()->detach();
                        } else {
                            $data::where('id', $id)->update([
                                'branch_id' => $branchIds[0],
                            ]);
                            $data->branches()->sync($branchIds);
                        }
                    }
                }
            } else if ($action == 'updateUserTeam') {
                // update grid
                $details = [];

                if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                    foreach ($requestBody['detail'][0]['users'] as $item) {
                        // IF not form LNJ 2 then use ID, else use ref_id
                        if (!$isFromLNJ2) {
                            $details[] = [
                                'id' => $item['detail_id'] ?? 0,
                                'user_id' => $item['user_id'],
                                'remark' => $item['remark'] ?? '-',
                            ];
                        } else {
                            $details[] = [
                                'user_id' => $item['user_id'],
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $item['ref_id'],
                                'id' => $item['id']
                            ];
                        }
                    }
                }

                if (!$isFromLNJ2 || $containRefId) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();
                } else {
                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->first();
                }



                if ($data) {

                    $data->update($requestBody);


                    if (empty($details)) {
                        $data->userTeamDetails()->delete();
                    } else {
                        // To delete data if id or ref_id not in payload
                        if (!$isFromLNJ2) {
                            $existingIds = collect($details)->pluck('id')->filter();
                            $data->userTeamDetails()->whereNotIn('id', $existingIds)->delete();
                        } else {
                            $idsNotToDelete = [];
                            foreach ($details as $item) {
                                if ($item['ref_id'] != null) {
                                    $idsNotToDelete[] = $data->userTeamDetails()->where('id', $item['ref_id'])->pluck('id')->first();
                                } else {
                                    $idsNotToDelete[] = $data->userTeamDetails()->where('ref_id', $item['id'])->pluck('id')->first();
                                }
                            }
                            $data->userTeamDetails()->whereNotIn('id', $idsNotToDelete)->delete();
                        }

                        foreach ($details as $detail) {
                            if (!$isFromLNJ2) {
                                // If id exist then update if not then create new
                                if ($detail['id'] != 0) {
                                    $data->userTeamDetails()->where('id', $detail['id'])->update($detail);
                                } else {
                                    $newUserTeamDetail = $data->userTeamDetails()->create($detail);
                                    $newUserTeamDetailIds[] = $newUserTeamDetail->id;
                                }
                            } else {
                                $refId = $detail['ref_id'];
                                $idsToCheck = $detail['id'];
                                $refIdLNJ3 = $detail['id'];
                                unset($detail['ref_id']);
                                unset($detail['id']);
                                if ($refId != null) {
                                    $affectedRows = $data->userTeamDetails()->where('id', $refId)->update($detail);
                                } else {
                                    $affectedRows = $data->userTeamDetails()->where('ref_id', $idsToCheck)->update($detail);
                                }
                                if ($affectedRows === 0) {
                                    $detail['ref_id'] = $refIdLNJ3;
                                    // If no rows were affected, the ref_id was not found, so create a new record
                                    $data->userTeamDetails()->create($detail);
                                }
                            }
                        }
                    }
                }
            } else if ($action == 'updatePrincipalGroup') {
                // update grid
                if (!$isFromLNJ2 || $containRefId) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();
                } else {
                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->first();
                }

                if ($data) {
                    $data->update($requestBody);

                    Principal::where('principal_group_id', $data->id)->update(['principal_group_id' => null]);

                    if (!empty($requestBody['detail'][0]['principals'])) {
                        // Jika detail tidak kosong, update principal_group_id di semua principals
                        // $principalIds = [];
                        // $refIds = [];

                        foreach ($requestBody['detail'][0]['principals'] as $detail) {
                            if (isset($detail['ref_id']) && $detail['ref_id'] !== null) {
                                // Lakukan sesuatu jika ref_id tersedia dan tidak sama dengan null
                                Principal::whereIn('id', [$detail['ref_id']])->update(['principal_group_id' => $id]);
                            } elseif (isset($detail['ref_id']) && $detail['ref_id'] == null) {
                                // Lakukan sesuatu jika ref_id tidak tersedia atau sama dengan null
                                Principal::whereIn('ref_id', [$detail['principal_id']])->update(['principal_group_id' => $id]);
                            } else if (!isset($detail['ref_id'])) {
                                // Lakukan sesuatu jika keduanya ref_id dan principal_id tidak tersedia
                                Principal::whereIn('id', [$detail['principal_id']])->update(['principal_group_id' => $id]);
                            }
                        }

                        //foreach ($requestBody['detail'][0]['principals'] as $detail) {

                        // // $principalIds[] = $detail['principal_id'];

                        //}



                        // if (!$isFromLNJ2 || $containRefId) {
                        //     Principal::whereIn('id', $principalIds)->update(['principal_group_id' => $id]);
                        // } else {
                        //     Principal::whereIn('ref_id', $principalIds)->update(['principal_group_id' => $id]);
                        // }
                    }
                }
            } else if ($action == 'updatePrincipal') {

                // update grid
                $detailComodities = [];
                $detailPics = [];
                $detailCategories = [];
                $detailAddresses = [];

                if (isset($requestBody['detail']) && $requestBody['detail'] != []) {

                    foreach ($requestBody['detail'][0]['principal_commodity'] as $item) {
                        // IF not form LNJ 2 then use ID, else use ref_id
                        if (!$isFromLNJ2) {
                            $detailComodities[] = [
                                'id' => $item['id'] ?? 0,
                                'name' => $item['name'] ?? '-',
                                'imo' => $item['imo'] ?? '-',
                                'un' => $item['un'] ?? '-',
                                'pck_grp' => $item['pck_grp'] ?? '-',
                                'fi_pt' => $item['fi_pt'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                            ];
                        } else {
                            $detailComodities[] = [
                                'ref_id' => $item['ref_id'],
                                'id' => $item['id'],
                                'name' => $item['name'] ?? '-',
                                'imo' => $item['imo'] ?? '-',
                                'un' => $item['un'] ?? '-',
                                'pck_grp' => $item['pck_grp'] ?? '-',
                                'fi_pt' => $item['fi_pt'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                            ];
                        }
                    }

                    foreach ($requestBody['detail'][1]['principal_pic'] as $item) {
                        // IF not form LNJ 2 then use ID, else use ref_id
                        if (!$isFromLNJ2) {
                            $detailPics[] = [
                                'id' => $item['id'] ?? 0,
                                'name' => $item['name'] ?? '-',
                                'phone' => $item['phone'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                            ];
                        } else {
                            $detailPics[] = [
                                'name' => $item['name'] ?? '-',
                                'phone' => $item['phone'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $item['ref_id'],
                                'id' => $item['id'],

                            ];
                        }
                    }

                    foreach ($requestBody['detail'][2]['principal_category'] as $item) {
                        if (!$isFromLNJ2) {
                            $detailCategories[] = [
                                'id' => $item['id'] ?? 0,
                                'principal_category_id' => $item['principal_category_id'],
                                'remark' => $item['remark'] ?? '-',
                            ];
                        } else {
                            $detailCategories[] = [
                                'principal_category_id' => $item['principal_category_id'],
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $item['ref_id'],
                                'id' => $item['id'],
                            ];
                        }
                    }

                    foreach ($requestBody['detail'][4]['address'] as $item) {
                        if ($item['is_visible']) {
                            $item['is_visible'] = 1;
                        } else {
                            $item['is_visible'] = 0;
                        }
                        // IF not form LNJ 2 then use ID, else use ref_id
                        if (!$isFromLNJ2) {
                            $detailAddresses[] = [
                                'id' => $item['id'] ?? 0,
                                'address' => $item['address']  ?? '-',
                                'pic' => $item['pic']  ?? '-',
                                'phone' => $item['phone']  ?? '-',
                                'email' => $item['email']  ?? '-',
                                'contact' => $item['contact'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                                'note' => $item['note'] ?? '-',
                                'district_id' => $item['district_id'],
                                'is_visible' => $item['is_visible'],
                            ];
                        } else {
                            $detailAddresses[] = [
                                'address' => $item['address']  ?? '-',
                                'pic' => $item['pic']  ?? '-',
                                'phone' => $item['phone']  ?? '-',
                                'email' => $item['email']  ?? '-',
                                'contact' => $item['contact'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                                'note' => $item['note'] ?? '-',
                                'is_visible' => $item['is_visible'],
                                'district_id' => $item['district_id'],
                                'ref_id' => $item['ref_id'],
                                'id' => $item['id'],

                            ];
                        }
                    }
                }

                // principals banks
                $bankNo = "";
                $bankName = "";
                if (isset($requestBody["bank_no"]) && isset($requestBody["bank_name"])) {
                    $bankNo = $requestBody['bank_no'];
                    $bankName = $requestBody['bank_name'];
                    unset($requestBody['bank_no']);
                    unset($requestBody['bank_name']);
                }

                if (!$isFromLNJ2 || $containRefId) {

                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();
                } else {

                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->first();
                }



                if ($data) {

                    $data->update($requestBody);

                    // Case bank = di LNJ3 table sendiri di LNJ2 masuk ke mhprincipal
                    if ($bankNo != "" && $bankName != "") {

                        $bankAccountPayload = [
                            'number' => $bankNo,
                            'name' => $bankName,
                        ];

                        $data->bankAccounts()->where('principal_id', $data->id)->update($bankAccountPayload);
                    }


                    if (empty($detailComodities)) {
                        $data->principalCommodities()->delete();
                    } else {
                        if (!$isFromLNJ2) {
                            $existingIds = collect($detailComodities)->pluck('id')->filter();
                            $data->principalCommodities()->whereNotIn('id', $existingIds)->delete();
                        } else {
                            $idsNotToDelete = [];
                            foreach ($detailComodities as $item) {
                                if ($item['ref_id'] != null) {
                                    $idsNotToDelete[] = $data->principalCommodities()->where('id', $item['ref_id'])->pluck('id')->first();
                                } else {
                                    $idsNotToDelete[] = $data->principalCommodities()->where('ref_id', $item['id'])->pluck('id')->first();
                                }
                            }
                            $data->principalCommodities()->whereNotIn('id', $idsNotToDelete)->delete();
                        }

                        //TODO: delete this previouse code
                        // foreach ($detailComodities as $detail) {

                        //     if ($detail['id'] != 0 && $detail['id'] !== null) {
                        //         $data->principalCommodities()->where('id', $detail['id'])->update($detail);
                        //     } else {

                        //         $detail['code'] = FormattingCodeHelper::formatCode('principal_commodity_category', "", [], [], [], null);
                        //         unset($detail['id']);
                        //         // dd($detail);
                        //         $newPrincipalCommodities = $data->principalCommodities()->create($detail);
                        //     }
                        // }
                        foreach ($detailComodities as $detail) {
                            if (!$isFromLNJ2) {
                                // If id exist then update if not then create new
                                if ($detail['id'] != 0) {
                                    $data->principalCommodities()->where('id', $detail['id'])->update($detail);
                                } else {
                                    $newPrincipalCommodities = $data->principalCommodities()->create($detail);
                                    $newPrincipalCommodityIds[] = $newPrincipalCommodities->id;
                                }
                            } else {
                                $refId = $detail['ref_id'];
                                $idsToCheck = $detail['id'];
                                $refIdLNJ3 = $detail['id'];
                                unset($detail['ref_id']);
                                unset($detail['id']);
                                if ($refId != null) {
                                    $affectedRows = $data->principalCommodities()->where('id', $refId)->update($detail);
                                } else {
                                    $affectedRows = $data->principalCommodities()->where('ref_id', $idsToCheck)->update($detail);
                                }
                                if ($affectedRows === 0) {
                                    $detail['ref_id'] = $refIdLNJ3;
                                    // If no rows were affected, the ref_id was not found, so create a new record
                                    $data->principalCommodities()->create($detail);
                                }
                            }
                        }
                    }

                    if (empty($detailPics)) {
                        $data->principalPics()->delete();
                    } else {
                        if (!$isFromLNJ2) {
                            $existingIds = collect($detailPics)->pluck('id')->filter();
                            $data->principalPics()->whereNotIn('id', $existingIds)->delete();
                        } else {
                            $idsNotToDelete = [];
                            foreach ($detailPics as $item) {
                                if ($item['ref_id'] != null) {
                                    $idsNotToDelete[] = $data->principalPics()->where('id', $item['ref_id'])->pluck('id')->first();
                                } else {
                                    $idsNotToDelete[] = $data->principalPics()->where('ref_id', $item['id'])->pluck('id')->first();
                                }
                            }
                            $data->principalPics()->whereNotIn('id', $idsNotToDelete)->delete();
                        }


                        foreach ($detailPics as $detail) {
                            if (!$isFromLNJ2) {
                                // If id exist then update if not then create new
                                if ($detail['id'] != 0) {
                                    $data->principalPics()->where('id', $detail['id'])->update($detail);
                                } else {
                                    $newPics = $data->principalPics()->create($detail);
                                    $newPicIds[] = $newPics->id;
                                }
                            } else {
                                $refId = $detail['ref_id'];
                                $idsToCheck = $detail['id'];
                                $refIdLNJ3 = $detail['id'];
                                unset($detail['ref_id']);
                                unset($detail['id']);
                                if ($refId != null) {
                                    $affectedRows = $data->principalPics()->where('id', $refId)->update($detail);
                                } else {
                                    $affectedRows = $data->principalPics()->where('ref_id', $idsToCheck)->update($detail);
                                }
                                if ($affectedRows === 0) {
                                    $detail['ref_id'] = $refIdLNJ3;
                                    // If no rows were affected, the ref_id was not found, so create a new record
                                    $data->principalPics()->create($detail);
                                }
                            }
                        }
                    }

                    if (empty($detailCategories)) {
                        $data->principalCategoryDetails()->delete();
                    } else {
                        if (!$isFromLNJ2) {
                            $existingIds = collect($detailCategories)->pluck('id')->filter();
                            $data->principalCategoryDetails()->whereNotIn('id', $existingIds)->delete();
                        } else {
                            $idsNotToDelete = [];
                            foreach ($detailCategories as $item) {
                                if ($item['ref_id'] != null) {
                                    $idsNotToDelete[] = $data->principalCategoryDetails()->where('id', $item['ref_id'])->pluck('id')->first();
                                } else {
                                    $idsNotToDelete[] = $data->principalCategoryDetails()->where('ref_id', $item['id'])->pluck('id')->first();
                                }
                            }
                            $data->principalCategoryDetails()->whereNotIn('id', $idsNotToDelete)->delete();
                        }

                        foreach ($detailCategories as $detail) {
                            if (!$isFromLNJ2) {
                                // If id exist then update if not then create new
                                if ($detail['id'] != 0) {
                                    $data->principalCategoryDetails()->where('id', $detail['id'])->update($detail);
                                } else {
                                    $newCategories = $data->principalCategoryDetails()->create($detail);
                                    $newCategoryIds[] = $newCategories->id;
                                }
                            } else {
                                $refId = $detail['ref_id'];
                                $idsToCheck = $detail['id'];
                                $refIdLNJ3 = $detail['id'];
                                unset($detail['ref_id']);
                                unset($detail['id']);
                                if ($refId != null) {
                                    $affectedRows = $data->principalCategoryDetails()->where('id', $refId)->update($detail);
                                } else {
                                    $affectedRows = $data->principalCategoryDetails()->where('ref_id', $idsToCheck)->update($detail);
                                }
                                if ($affectedRows === 0) {
                                    $detail['ref_id'] = $refIdLNJ3;
                                    // If no rows were affected, the ref_id was not found, so create a new record
                                    $data->principalCategoryDetails()->create($detail);
                                }
                            }
                        }
                    }

                    if (empty($detailAddresses)) {
                        $data->principalAddresses()->delete();
                    } else {
                        // To delete data if id or ref_id not in payload
                        if (!$isFromLNJ2) {
                            $existingIds = collect($detailAddresses)->pluck('id')->filter();
                            $data->principalAddresses()->whereNotIn('id', $existingIds)->delete();
                        } else {
                            $idsNotToDelete = [];
                            foreach ($detailAddresses as $item) {
                                if ($item['ref_id'] != null) {
                                    $idsNotToDelete[] = $data->principalAddresses()->where('id', $item['ref_id'])->pluck('id')->first();
                                } else {
                                    $idsNotToDelete[] = $data->principalAddresses()->where('ref_id', $item['id'])->pluck('id')->first();
                                }
                            }
                            $data->principalAddresses()->whereNotIn('id', $idsNotToDelete)->delete();
                        }

                        foreach ($detailAddresses as $detail) {
                            if (!$isFromLNJ2) {
                                // If id exist then update if not then create new
                                if ($detail['id'] != 0) {
                                    $data->principalAddresses()->where('id', $detail['id'])->update($detail);
                                } else {
                                    $newAddresses = $data->principalAddresses()->create($detail);
                                    $newAddressIds[] = $newAddresses->id;
                                }
                            } else {
                                $refId = $detail['ref_id'];
                                $idsToCheck = $detail['id'];
                                $refIdLNJ3 = $detail['id'];
                                unset($detail['ref_id']);
                                unset($detail['id']);
                                if ($refId != null) {
                                    $affectedRows = $data->principalAddresses()->where('id', $refId)->update($detail);
                                } else {
                                    $affectedRows = $data->principalAddresses()->where('ref_id', $idsToCheck)->update($detail);
                                }
                                if ($affectedRows === 0) {
                                    $detail['ref_id'] = $refIdLNJ3;
                                    // If no rows were affected, the ref_id was not found, so create a new record
                                    $data->principalAddresses()->create($detail);
                                }
                            }
                        }
                    }
                }
            } else if ($action == 'updateQuotation') {
                // update grid
                $detailCargo = [];
                $detailBuy = [];
                $detailSell = [];

                // dd($requestBody['detail']);
                if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                    if ($requestBody['detail'][0]['cargo_details']) {
                        foreach ($requestBody['detail'][0]['cargo_details'] as $item) {
                            if (!$isFromLNJ2) {
                                $detailCargo[] = [
                                    'id' => $item['detail_id'] ?? 0,
                                    'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                                    'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                                    'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                                    'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                                    'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                                    'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                                    'quantity' =>  isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                                    'total' => $item['total'] ?? '-',
                                    'remark' => $item['remark'] ?? '-',
                                    'container_type_id' => $item['container_type_id'] ?? null,
                                    'container_size_id' => $item['container_size_id'] ?? null
                                ];
                            } else {
                                $detailCargo[] = [
                                    'ref_id' => $item['ref_id'],
                                    'id' => $item['id'],
                                    'package_length' => isset($item['package_length']) && $item['package_length'] !== '' ? $item['package_length'] : 0,
                                    'package_width' => isset($item['package_width']) && $item['package_width'] !== '' ? $item['package_width'] : 0,
                                    'package_height' => isset($item['package_height']) && $item['package_height'] !== '' ? $item['package_height'] : 0,
                                    'package_weight' => isset($item['package_weight']) && $item['package_weight'] !== '' ? $item['package_weight'] : 0,
                                    'volume' => isset($item['volume']) && $item['volume'] !== '' ? $item['volume'] : 0,
                                    'gross_weight' => isset($item['gross_weight']) && $item['gross_weight'] !== '' ? $item['gross_weight'] : 0,
                                    'quantity' =>  isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : 0,
                                    'total' => $item['total'] ?? '-',
                                    'remark' => $item['remark'] ?? '-',
                                    'container_type_id' => $item['container_type_id'] ?? null,
                                    'container_size_id' => $item['container_size_id'] ?? null
                                ];
                            }
                        }
                    }
                    if ($requestBody['detail'][1]['service_buy']) {
                        foreach ($requestBody['detail'][1]['service_buy'] as $item) {
                            if (!$isFromLNJ2) {
                                $detailBuy[] = [
                                    'id' => $item['detail_id'],
                                    'service_desc' => $item['service_desc'] ?? '-',
                                    'service_price_desc' => $item['service_price_desc'] ?? '-',
                                    'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                                    'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                    'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                    'remark' => $item['remark'] ?? '-',
                                    'charge_segment' => $item['charge_segment'] ?? '-',
                                    'service_category' => $item['service_category'] ?? 'BUY',
                                    'service_group_id' => $item['service_group_id'] ?? null,
                                    'service_price_id' => $item['service_price_id'] ?? null,
                                    'service_id' => $item['service_id'] ?? null,
                                    'currency_id' => $item['currency_id'] ?? null,
                                    'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                    'principal_id' => $item['principal_id'] ?? null,
                                    'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                                ];
                            } else {
                                $detailBuy[] = [
                                    'ref_id' => $item['ref_id'],
                                    'id' => $item['id'],
                                    'service_desc' => $item['service_desc'] ?? '-',
                                    'service_price_desc' => $item['service_price_desc'] ?? '-',
                                    'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                                    'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                    'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                    'remark' => $item['remark'] ?? '-',
                                    'charge_segment' => $item['charge_segment'] ?? '-',
                                    'service_category' => $item['service_category'] ?? 'BUY',
                                    'service_group_id' => $item['service_group_id'] ?? null,
                                    'service_price_id' => $item['service_price_id'] ?? null,
                                    'service_id' => $item['service_id'] ?? null,
                                    'currency_id' => $item['currency_id'] ?? null,
                                    'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                    'principal_id' => $item['principal_id'] ?? null,
                                    'base_price' => isset($item['base_price']) && $item['base_price'] !== '' ? $item['base_price'] : 0,
                                ];
                            }
                        }
                    }
                    if ($requestBody['detail'][2]['service_sell']) {
                        foreach ($requestBody['detail'][2]['service_sell'] as $item) {
                            if (!$isFromLNJ2) {
                                $detailSell[] = [
                                    'id' => $item['detail_id'],
                                    'service_desc' => $item['service_desc'] ?? '-',
                                    'service_price_desc' => $item['service_price_desc'] ?? '-',
                                    'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                                    'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                    'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                    'remark' => $item['remark'] ?? '-',
                                    'charge_segment' => $item['charge_segment'] ?? '-',
                                    'service_category' => $item['service_category'] ?? 'SELL',
                                    'service_id' => $item['service_id'] ?? null,
                                    'service_group_id' => $item['service_group_id'] ?? null,
                                    'service_price_id' => $item['service_price_id'] ?? null,
                                    'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                                    'principal_id' => $item['principal_id'] ?? null,
                                    'currency_id' => $item['currency_id'] ?? null,
                                    'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                                    'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                                    'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                                ];
                            } else {
                                $detailSell[] = [
                                    'ref_id' => $item['ref_id'],
                                    'id' => $item['id'],
                                    'service_desc' => $item['service_desc'] ?? '-',
                                    'service_price_desc' => $item['service_price_desc'] ?? '-',
                                    'supplier_service_id' => isset($item['supplier_service_id']) && $item['supplier_service_id'] !== '' ? $item['supplier_service_id'] : 0,
                                    'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                    'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                    'remark' => $item['remark'] ?? '-',
                                    'charge_segment' => $item['charge_segment'] ?? '-',
                                    'service_category' => $item['service_category'] ?? 'SELL',
                                    'service_id' => $item['service_id'] ?? null,
                                    'service_group_id' => $item['service_group_id'] ?? null,
                                    'service_price_id' => $item['service_price_id'] ?? null,
                                    'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                                    'principal_id' => $item['principal_id'] ?? null,
                                    'currency_id' => $item['currency_id'] ?? null,
                                    'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : 0,
                                    'sales_price' => isset($item['sales_price']) && $item['sales_price'] !== '' ? $item['sales_price'] : 0,
                                    'total_amount' => isset($item['total_amount']) && $item['total_amount'] !== '' ? $item['total_amount'] : 0,
                                ];
                            }
                        }
                    }
                }

                if (!$isFromLNJ2 || $containRefId) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();
                } else {
                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->first();
                }

                if ($data) {
                    $data->update($requestBody);
                    // To delete data if id or ref_id not in payload

                    if (!$isFromLNJ2) {
                        $existingIds = collect($detailCargo)->pluck('id')->filter();
                        $data->rfqCargoes()->whereNotIn('id', $existingIds)->delete();
                    } else {
                        $idsCargoNotToDelete = [];
                        foreach ($detailCargo as $item) {
                            if ($item['ref_id'] != null) {
                                $idsCargoNotToDelete[] = $data->rfqCargoes()->where('id', $item['ref_id'])->pluck('id')->first();
                            } else {
                                $idsCargoNotToDelete[] = $data->rfqCargoes()->where('ref_id', $item['id'])->pluck('id')->first();
                            }
                        }
                        $data->rfqCargoes()->whereNotIn('id', $idsCargoNotToDelete)->delete();

                        // $existingIds = collect($detailCargo)->pluck('ref_id')->filter();
                        // $data->rfqCargoes()->whereNotIn('ref_id', $existingIds)->delete();
                    }

                    foreach ($detailCargo as $detail) {
                        if (!$isFromLNJ2) {
                            // If id exist then update if not then create new
                            if ($detail['id'] != 0) {
                                $data->rfqCargoes()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newCargoDetail = $data->rfqCargoes()->create($detail);
                                $newCargoDetailIds[] = $newCargoDetail->id;
                            }
                        } else {
                            $refId = $detail['ref_id'];
                            $idsToCheck = $detail['id'];
                            $refIdLNJ3 = $detail['id'];
                            unset($detail['ref_id']);
                            unset($detail['id']);
                            if ($refId != null) {
                                $affectedRows = $data->rfqCargoes()->where('id', $refId)->update($detail);
                            } else {
                                $affectedRows = $data->rfqCargoes()->where('ref_id', $idsToCheck)->update($detail);
                            }
                            if ($affectedRows === 0) {
                                $detail['ref_id'] = $refIdLNJ3;
                                // If no rows were affected, the ref_id was not found, so create a new record
                                $data->rfqCargoes()->create($detail);
                            }

                            // $affectedRows = $data->rfqCargoes()->where('ref_id', $detail['ref_id'])->update($detail);
                            // if ($affectedRows === 0) {
                            //     // If no rows were affected, the ref_id was not found, so create a new record
                            //     $data->rfqCargoes()->create($detail);
                            // }
                        }
                    }

                    // To delete data from service buy if id or ref_id not in payload
                    if (!$isFromLNJ2) {
                        $existingIds = collect($detailBuy)->pluck('id')->filter();
                        $data->rfqDetails()->whereNotIn('id', $existingIds)->where('service_category', '=', 'BUY')->delete();
                    } else {
                        $idsBuyNotToDelete = [];
                        foreach ($detailBuy as $item) {
                            if ($item['ref_id'] != null) {
                                $idsBuyNotToDelete[] = $data->rfqDetails()->where('id', $item['ref_id'])->pluck('id')->first();
                            } else {
                                $idsBuyNotToDelete[] = $data->rfqDetails()->where('ref_id', $item['id'])->pluck('id')->first();
                            }
                        }
                        $data->rfqDetails()->whereNotIn('id', $idsBuyNotToDelete)->where('service_category', '=', 'BUY')->delete();

                        // $existingIds = collect($detailBuy)->pluck('ref_id')->filter();
                        // $data->rfqDetails()->whereNotIn('ref_id', $existingIds)->where('service_category', '=', 'BUY')->delete();
                    }

                    foreach ($detailBuy as $detail) {
                        if (!$isFromLNJ2) {
                            // If id exist then update if not then create new
                            if ($detail['id'] != 0) {
                                $data->rfqDetails()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newBuyDetail = $data->rfqDetails()->create($detail);
                                $newBuyDetailIds[] = $newBuyDetail->id;
                            }
                        } else {
                            $refId = $detail['ref_id'];
                            $idsToCheck = $detail['id'];
                            $refIdLNJ3 = $detail['id'];
                            unset($detail['ref_id']);
                            unset($detail['id']);
                            if ($refId != null) {
                                $affectedRows = $data->rfqDetails()->where('id', $refId)->update($detail);
                            } else {
                                $affectedRows = $data->rfqDetails()->where('ref_id', $idsToCheck)->update($detail);
                            }
                            if ($affectedRows === 0) {
                                $detail['ref_id'] = $refIdLNJ3;
                                // If no rows were affected, the ref_id was not found, so create a new record
                                $data->rfqDetails()->create($detail);
                            }

                            // $affectedRows = $data->rfqDetails()->where('ref_id', $detail['ref_id'])->update($detail);
                            // if ($affectedRows === 0) {
                            //     // If no rows were affected, the ref_id was not found, so create a new record
                            //     $data->rfqDetails()->create($detail);
                            // }
                        }
                    }

                    // To delete data from service sell if id or ref_id not in payload
                    if (!$isFromLNJ2) {
                        $existingIds = collect($detailSell)->pluck('id')->filter();
                        $data->rfqDetails()->whereNotIn('id', $existingIds)->where('service_category', '=', 'SELL')->delete();
                    } else {
                        $idsSellNotToDelete = [];
                        foreach ($detailSell as $item) {
                            if ($item['ref_id'] != null) {
                                $idsSellNotToDelete[] = $data->rfqDetails()->where('id', $item['ref_id'])->pluck('id')->first();
                            } else {
                                $idsSellNotToDelete[] = $data->rfqDetails()->where('ref_id', $item['id'])->pluck('id')->first();
                            }
                        }

                        $data->rfqDetails()->whereNotIn('id', $idsSellNotToDelete)->where('service_category', '=', 'SELL')->delete();
                        // $existingIds = collect($detailSell)->pluck('ref_id')->filter();
                        // $data->rfqDetails()->whereNotIn('ref_id', $existingIds)->where('service_category', '=', 'SELL')->delete();
                    }
                    // dd($detailSell);
                    foreach ($detailSell as $detail) {
                        if (!$isFromLNJ2) {
                            // If id exist then update if not then create new
                            if ($detail['id'] != 0) {
                                $data->rfqDetails()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newSellDetail = $data->rfqDetails()->create($detail);
                                $newSellDetailIds[] = $newSellDetail->id;
                            }
                        } else {
                            $refId = $detail['ref_id'];
                            $idsToCheck = $detail['id'];
                            $refIdLNJ3 = $detail['id'];
                            unset($detail['ref_id']);
                            unset($detail['id']);
                            if ($refId != null) {
                                $affectedRows = $data->rfqDetails()->where('id', $refId)->update($detail);
                            } else {
                                $affectedRows = $data->rfqDetails()->where('ref_id', $idsToCheck)->update($detail);
                            }
                            if ($affectedRows === 0) {
                                $detail['ref_id'] = $refIdLNJ3;
                                // If no rows were affected, the ref_id was not found, so create a new record
                                $data->rfqDetails()->create($detail);
                            }

                            // $affectedRows = $data->rfqDetails()->where('ref_id', $detail['ref_id'])->update($detail);
                            // if ($affectedRows === 0) {
                            //     // If no rows were affected, the ref_id was not found, so create a new record
                            //     $data->rfqDetails()->create($detail);
                            // }
                        }
                    }
                }
            } else if ($action == 'updateStatusQuotation') {
                if (isset($requestBody['is_approve'])) {
                    // Logic untuk update rfq_detail sesuai sp di LNJ 2
                    // Fetch records
                    $rfqDetailsService = DB::table('rfq_details as a')
                        ->select('id')
                        ->where('rfq_id', $id)
                        ->where('service_id', null)
                        ->where('service_category', 'SELL')
                        ->get();

                    // Update records
                    foreach ($rfqDetailsService as $detail) {
                        DB::table('rfq_details')
                            ->where('id', $detail->id)
                            ->update([
                                'service_id' => $this->generateRfqDetailServiceId($detail->id),
                            ]);
                    }

                    // // Fetch records
                    // $rfqDetailsPrice = DB::table('rfq_details as a')
                    // ->select('id')
                    // ->where('rfq_id', $id)
                    // ->where('service_price_id', null)
                    // ->where('service_category', 'SELL')
                    // ->get();

                    // // Update records
                    // foreach ($rfqDetailsPrice as $detail) {
                    // DB::table('rfq_details')
                    //     ->where('id', $detail->id)
                    //     ->update([
                    //         'service_id' => $this->generateRfqDetailServicePriceId($detail->id),
                    //     ]);
                    // }

                    if ($requestBody['is_approve'] == 1) {
                        $newData = [
                            'approved_by' => auth()->id(),
                            'is_approve' => true,
                            'approved_at' => now(),
                        ];
                    } else {
                        $newData = [
                            'approved_by' => auth()->id(),
                            'is_approve' => false,
                            'approved_at' => now(),
                        ];
                    }
                } else if (isset($requestBody['is_finish_quot'])) {
                    if ($requestBody['is_finish_quot'] == 1) {
                        $newData = [
                            'is_finish_quot' => true,
                        ];
                    } else {
                        $newData = [
                            'is_finish_quot' => false,
                        ];
                    }
                }
                if (!$isFromLNJ2 || $containRefId) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update($requestBody);
                } else {
                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->update($requestBody);
                }
            } else if ($action == 'updatePreOrder') {
                // update grid
                $detailParties = [];
                $detailPackings = [];
                $detailItemShipment = [];
                $detailContainer = [];

                if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                    foreach ($detailPayload as $value) {

                        if (array_key_exists('detail_party', $value)) {

                            if (!empty($value)) {
                                foreach ($detailPayload[0]['detail_party'] as $item) {
                                    $detailParties[] = [
                                        'container_type_id' => $item['container_type_id'] ?? 0,
                                        'container_size_id' => $item['container_size_id'] ?? 0,
                                        'qty' => $item['qty'] ?? 4,
                                        'remark' => $item['remark'] ?? '-'
                                    ];
                                }
                                $data->preOrderParty()->createMany($detailParties);
                            }
                        } elseif (array_key_exists('detail_packing', $value)) {
                            if (!empty($value)) {
                                foreach ($detailPayload[1]['detail_packing'] as $item) {
                                    $detailPackings[] = [
                                        'packing_detail_id' => $item['packing_detail_id'] ?? null,
                                        'container_size_id' => $item['container_size_id'] ?? null,
                                        'package_type' => $item['package_type'] ?? 'C',
                                        'packing_no' => $item['packing_no'] ?? '',
                                        'packing_size' => $item['packing_size'] ?? '',
                                        'packing_type' => $item['packing_type'] ?? '',
                                        'uom' => $item['uom'] ?? '',
                                        'qty' => $item['qty'] ?? null,
                                        'seal_no' => $item['seal_no'] ?? '',
                                        'etd_factory' => $item['etd_factory'] ?? now(),
                                        'parent_id' => $item['parent_id'] ?? null,
                                        'shipment_item_id' => $item['shipment_item_id'] ?? null,
                                        'is_in_container' => $item['is_in_container'] ?? false,
                                        'remark' => $item['remark'] ?? '-'
                                    ];
                                }
                                $data->posPackingDetail()->createMany($detailPackings);
                            }
                        } elseif (array_key_exists('detail_item_shipment', $value)) {
                            if (!empty($value)) {
                                foreach ($detailPayload[2]['detail_item_shipment'] as $item) {
                                    $detailItemShipment[] = [
                                        'item_shipment_id' => $item['item_shipment_id'] ?? null,
                                        'po_number' => $item['po_number'] ?? '',
                                        'po_item_line' => $item['po_item_line'] ?? 0,
                                        'po_item_code' => $item['po_item_code'] ?? '',
                                        'material_description' => $item['material_description'] ?? '',
                                        'quantity_confirmed' => $item['quantity_confirmed'] ?? 0,
                                        'quantity_shipped' => $item['quantity_shipped'] ?? 0,
                                        'quantity_arrived' => $item['quantity_arrived'] ?? 0,
                                        'quantity_balance' => $item['quantity_balance'] ?? 0,
                                        'unit_qty_id' => $item['unit_qty_id'] ?? null,
                                        'cargo_readiness' => $item['cargo_readiness'] ?? null,
                                        'delivery_address_id' => $item['delivery_address_id'] ?? 0,
                                        'remark' => $item['remark'] ?? '-'
                                    ];
                                }
                                $data->posShipmentItem()->createMany($detailItemShipment);
                            }
                        } else {
                            if (!empty($value)) {
                                foreach ($detailPayload[3]['detail_container'] as $item) {
                                    $detailContainer[] = [
                                        'container_id' => $item['container_id'] ?? null,
                                        'container_type_id' => $item['container_type_id'] ?? null,
                                        'container_size_id' => $item['container_size_id'] ?? null,
                                        'port_id' => $item['port_id'] ?? null,
                                        'job_order_detail_id' => $item['job_order_detail_id'] ?? null,
                                        'principal_depot_id' => $item['principal_depot_id'] ?? null,
                                        'container_code' => $item['container_code'] ?? null,
                                        'container_seal' => $item['container_seal'] ?? null,
                                        'fmgs_start_date' => $item['fmgs_start_date'] ?? null,
                                        'fmgs_finish_date' => $item['fmgs_finish_date'] ?? null,
                                        'depo_in_date' => $item['depo_in_date'] ?? null,
                                        'depo_out_date' => $item['depo_out_date'] ?? null,
                                        'port_date' => $item['port_date'] ?? null,
                                        'disassemble_date' => $item['disassemble_date'] ?? null,
                                        'return_depo_date' => $item['return_depo_date'] ?? null,
                                        'pickup_date' => $item['pickup_date'] ?? null,
                                        'port_gatein_gate' => $item['port_gatein_gate'] ?? null,
                                        'total_pkg' => $item['total_pkg'] ?? null,
                                        'grossweight' => $item['grossweight'] ?? null,
                                        'netweight' => $item['netweight'] ?? null,
                                        'measurement' => $item['measurement'] ?? null,
                                        'dem' => $item['dem'] ?? null,
                                        'currency_dem_id' => $item['currency_dem_id'] ?? null,
                                        'rep' => $item['rep'] ?? null,
                                        'currency_rep_id' => $item['currency_rep_id'] ?? null,


                                    ];
                                }

                                $data->preOrderContainer()->createMany($detailContainer);
                            }
                        }
                    }
                }

                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->first();

                if ($data) {
                    $data->update($requestBody);

                    if (empty($detailParties)) {
                        $data->preOrderParty()->delete();
                    } else {
                        $existingIds = collect($detailParties)->pluck('id')->filter();
                        $data->preOrderParty()->whereNotIn('id', $existingIds)->delete();
                        foreach ($detailParties as $detail) {

                            if ($detail['id'] != 0) {
                                $data->preOrderParty()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newDetailParties = $data->preOrderParty()->create($detail);
                                // $newDetailParties[] = $newDetailParties->id;
                            }
                        }
                    }

                    if (empty($detailPackings)) {
                        $data->posPackingDetail()->delete();
                    } else {

                        $existingIds = collect($detailPackings)->pluck('id')->filter();
                        $data->posPackingDetail()->whereNotIn('id', $existingIds)->delete();
                        foreach ($detailPackings as $detail) {
                            if ($detail['id'] != 0) {
                                $data->posPackingDetail()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newDetailPackings = $data->posPackingDetail()->create($detail);
                                // $newDetailPackings[] = $newDetailPackings->id;
                            }
                        }
                    }

                    if (empty($detailItemShipment)) {
                        $data->posShipmentItem()->delete();
                    } else {
                        $existingIds = collect($detailItemShipment)->pluck('id')->filter();
                        $data->posShipmentItem()->whereNotIn('id', $existingIds)->delete();
                        foreach ($detailItemShipment as $detail) {
                            if ($detail['id'] != 0) {
                                $data->posShipmentItem()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newDetailItemShipment = $data->posShipmentItem()->create($detail);
                                // $newDetailItemShipment[] = $newDetailItemShipment->id;
                            }
                        }
                    }

                    if (empty($detailContainer)) {
                        $data->preOrderContainer()->delete();
                    } else {
                        $existingIds = collect($detailContainer)->pluck('id')->filter();
                        $data->preOrderContainer()->whereNotIn('id', $existingIds)->delete();
                        foreach ($detailContainer as $detail) {
                            if ($detail['id'] != 0) {
                                $data->preOrderContainer()->where('id', $detail['id'])->update($detail);
                            } else {
                                $newDetailContainer = $data->preOrderContainer()->create($detail);
                                // $newDetailContainer[] = $newDetailContainer->id;
                            }
                        }
                    }
                }
            } else if ($action == 'approvePreOrder') {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update([
                    'approved_by' => auth()->id(),
                    'is_approve' => true,
                    'approved_at' => now(),
                ]);
            } else if ($action == 'dissaprovePreOrder') {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update([
                    'rejected_by' => auth()->id(),
                    'is_approve' => false,
                    'rejected_at' => now(),
                ]);
            } else {
                if (!$isFromLNJ2 || $containRefId) {
                    $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update($requestBody);
                } else {
                    $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->update($requestBody);
                }
            }
            return $data;
        });


        // Get id dari yang baru dicreate buat dikirim ke lnj 2 sebagai ref_id
        if ($action == 'updateUserTeam') {
            if (!$isFromLNJ2 || $containRefId) {
                $newUserTeamDetailIds = $data->userTeamDetails()->pluck('id')->toArray();
            }
        } else if ($action == 'updateQuotation') {
            if (!$isFromLNJ2 || $containRefId) {
                $newCargoDetailIds = $data->rfqCargoes()->pluck('id')->toArray();
                $newBuyDetailIds = $data->rfqDetails()->where('service_category', '=', 'BUY')->pluck('id')->toArray();
                $newSellDetailIds = $data->rfqDetails()->where('service_category', '=', 'SELL')->pluck('id')->toArray();
            }
        } else if ($action == 'updatePrincipal') {
            if (!$isFromLNJ2 || $containRefId) {
                $newPrincipalCommodityIds = $data->principalCommodities()->pluck('id')->toArray();
                $newPicIds = $data->principalPics()->pluck('id')->toArray();
                $newCategoryIds = $data->principalCategoryDetails()->pluck('id')->toArray();
                $newAddressIds = $data->principalAddresses()->pluck('id')->toArray();
            }
        }

        // send data to LNJ V 2
        if (!$isFromLNJ2) {
            $data = $this->modelName($actionsToModel[$action])::where('id', $id)->get();
            if ($data) {
                if ($action == 'updatePrincipal') {
                    //principal id
                    $itemId = 0;
                    foreach ($data as $item) {
                        $itemId = $item->id;
                    }

                    $bankAccounts = PrincipalBankAccount::select('number AS bank_no', 'name AS bank_name')
                        ->where('principal_id', $itemId)
                        ->first();

                    if ($bankAccounts) {
                        $bankNo = $bankAccounts->bank_no;
                        $bankName = $bankAccounts->bank_name;

                        $data->each(function ($item) use ($bankNo, $bankName) {
                            $item->bank_no = $bankNo;
                            $item->bank_name = $bankName;
                        });
                    }
                }

                $objPayload = [
                    'data' => $data,
                ];

                if ($action == 'updateUserTeam') {
                    $details = [];
                    if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                        $count = 0;
                        foreach ($requestBody['detail'][0]['users'] as $item) {
                            // Kalau dari payload ada id, maka jadi ref_id kalau tidak maka akan diambil dari newUserTeamDetailIds
                            // if (isset($item['detail_id'])) {
                            //     $ids = $item['detail_id'];
                            // } else {
                            //     $ids = $newUserTeamDetailIds[$count];
                            //     $count += 1;
                            // }
                            $ids = $newUserTeamDetailIds[$count];
                            $count += 1;
                            $crossId = $this->modelName($actionsToModel['updateUserTeamDetail'])::where('id', $ids)->pluck('ref_id')->first();
                            $details[] = [
                                'user_id' => $item['user_id'],
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                            ];
                        }
                        $detailUserArray = [
                            'users' => $details,
                        ];
                        $objPayload['detail'] = [$detailUserArray];
                    }
                } else if ($action == 'updateQuotation') {
                    $detailCargo = [];
                    $detailBuy = [];
                    $detailSell = [];

                    if (isset($requestBody['detail']) && $requestBody['detail'] != []) {
                        if ($requestBody['detail'][0]['cargo_details']) {
                            $count = 0;
                            foreach ($requestBody['detail'][0]['cargo_details'] as $item) {
                                // Kalau dari payload ada id, maka jadi ref_id kalau tidak maka akan diambil dari newUserTeamDetailIds
                                // if (isset($item['id'])) {
                                //     $ids = $item['id'];
                                // } else {
                                //     $ids = $newCargoDetailIds[$count];
                                //     $count += 1;
                                // }
                                // $detailCargo[] = [
                                //     'ref_id' => $ids ?? 0,
                                //     'package_length' => $item['package_length'] ?? 0,
                                //     'package_width' => $item['package_width'] ?? 0,
                                //     'package_height' => $item['package_height'] ?? 0,
                                //     'package_weight' => $item['package_weight'] ?? 0,
                                //     'volume' => $item['volume'] ?? 0,
                                //     'gross_weight' => $item['gross_weight'] ?? 0,
                                //     'quantity' => $item['quantity'] ?? 0,
                                //     'total' => $item['total'] ?? 0,
                                //     'remark' => $item['remark'] ?? '-',
                                //     'container_type_id' => $item['container_type_id'] ?? null,
                                //     'container_size_id' => $item['container_size_id'] ?? null
                                // ];

                                $ids = $newCargoDetailIds[$count];
                                $count += 1;
                                $crossId = $this->modelName($actionsToModel['updateRfqCargo'])::where('id', $ids)->pluck('ref_id')->first();
                                $detailCargo[] = [
                                    'package_length' => $item['package_length'] ?? 0,
                                    'package_width' => $item['package_width'] ?? 0,
                                    'package_height' => $item['package_height'] ?? 0,
                                    'package_weight' => $item['package_weight'] ?? 0,
                                    'volume' => $item['volume'] ?? 0,
                                    'gross_weight' => $item['gross_weight'] ?? 0,
                                    'quantity' => $item['quantity'] ?? 0,
                                    'total' => $item['total'] ?? 0,
                                    'remark' => $item['remark'] ?? '-',
                                    'container_type_id' => $item['container_type_id'] ?? null,
                                    'container_size_id' => $item['container_size_id'] ?? null,
                                    'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                    'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                                ];
                            }
                        }
                        if ($requestBody['detail'][1]['service_buy']) {
                            $count = 0;
                            foreach ($requestBody['detail'][1]['service_buy'] as $item) {
                                // if (isset($item['id'])) {
                                //     $ids = $item['id'];
                                // } else {
                                //     $ids = $newBuyDetailIds[$count];
                                //     $count += 1;
                                // }
                                // $detailBuy[] = [
                                //     'ref_id' => $ids ?? 0,
                                //     'service_desc' => $item['service_desc'] ?? '-',
                                //     'service_price_desc' => $item['service_price_desc'] ?? '-',
                                //     'supplier_service_id' => $item['supplier_service_id'] ?? 0,
                                //     'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                //     'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                //     'remark' => $item['remark'] ?? '-',
                                //     'charge_segment' => $item['charge_segment'] ?? '-',
                                //     'service_category' => $item['service_category'] ?? 'BUY',
                                //     'service_group_id' => $item['service_group_id'] ?? null,
                                //     'service_price_id' => $item['service_price_id'] ?? null,
                                //     'currency_id' => $item['currency_id'] ?? null,
                                //     'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                //     'base_price' => $item['base_price'] ?? '',
                                // ];

                                $ids = $newBuyDetailIds[$count];
                                $count += 1;
                                $crossId = $this->modelName($actionsToModel['updateRfqServiceBuy'])::where('id', $ids)->pluck('ref_id')->first();
                                $detailBuy[] = [
                                    'service_desc' => $item['service_desc'] ?? '-',
                                    'service_price_desc' => $item['service_price_desc'] ?? '-',
                                    'supplier_service_id' => $item['supplier_service_id'] ?? 0,
                                    'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                    'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                    'remark' => $item['remark'] ?? '-',
                                    'charge_segment' => $item['charge_segment'] ?? '-',
                                    'service_category' => $item['service_category'] ?? 'BUY',
                                    'service_group_id' => $item['service_group_id'] ?? null,
                                    'service_price_id' => $item['service_price_id'] ?? null,
                                    'currency_id' => $item['currency_id'] ?? null,
                                    'costing_currency_id' => $item['costing_currency_id'] ?? null,
                                    'base_price' => $item['base_price'] ?? '',
                                    'principal_id' => $item['principal_id'] ?? null,
                                    'service_id' => $item['service_id'] ?? null,
                                    'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                    'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                                ];
                            }
                        }
                        if ($requestBody['detail'][2]['service_sell']) {
                            $count = 0;
                            foreach ($requestBody['detail'][2]['service_sell'] as $item) {
                                // if (isset($item['id'])) {
                                //     $ids = $item['id'];
                                // } else {
                                //     $ids = $newSellDetailIds[$count];
                                //     $count += 1;
                                // }
                                // $detailSell[] = [
                                //     'ref_id' => $ids ?? 0,
                                //     'service_desc' => $item['service_desc'] ?? '-',
                                //     'service_price_desc' => $item['service_price_desc'] ?? '-',
                                //     'supplier_service_id' => $item['supplier_service_id'] ?? 0,
                                //     'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                //     'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                //     'remark' => $item['remark'] ?? '-',
                                //     'charge_segment' => $item['charge_segment'] ?? '-',
                                //     'service_category' => $item['service_category'] ?? 'SELL',
                                //     'service_id' => $item['service_id'] ?? null,
                                //     'principal_id' => $item['principal_id'] ?? null,
                                //     'currency_id' => $item['currency_id'] ?? null,
                                //     'qty' => $item['qty'] ?? 0,
                                //     'sales_price' => $item['sales_price'] ?? 0,
                                //     'total_amount' => $item['total_amount'] ?? 0
                                // ];

                                $ids = $newSellDetailIds[$count];
                                $count += 1;
                                $crossId = $this->modelName($actionsToModel['updateRfqServiceSell'])::where('id', $ids)->pluck('ref_id')->first();
                                $detailSell[] = [
                                    'service_desc' => $item['service_desc'] ?? '-',
                                    'service_price_desc' => $item['service_price_desc'] ?? '-',
                                    'supplier_service_id' => $item['supplier_service_id'] ?? 0,
                                    'transaction_date' => $item['transaction_date'] ?? date("Y-m-d"),
                                    'valid_until' => $item['valid_until'] ?? date("Y-m-d"),
                                    'remark' => $item['remark'] ?? '-',
                                    'charge_segment' => $item['charge_segment'] ?? '-',
                                    'service_category' => $item['service_category'] ?? 'SELL',
                                    'service_id' => $item['service_id'] ?? null,
                                    'principal_id' => $item['principal_id'] ?? null,
                                    'currency_id' => $item['currency_id'] ?? null,
                                    'measurement_unit_id' => $item['measurement_unit_id'] ?? null,
                                    'qty' => $item['qty'] ?? 0,
                                    'sales_price' => $item['sales_price'] ?? 0,
                                    'total_amount' => $item['total_amount'] ?? 0,
                                    'service_price_id' => $item['service_price_id'] ?? null,
                                    'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                    'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                                ];
                            }
                        }
                        $detailArray = [
                            'cargo_details' => $detailCargo,
                            'service_buy' => $detailBuy,
                            'service_sell' => $detailSell,
                        ];
                        $objPayload['detail'] = [$detailArray];
                    }
                } else if ($action == 'updatePrincipal') {
                    // update grid
                    $detailComodities = [];
                    $detailPics = [];
                    $detailCategories = [];
                    $detailAddresses = [];
                    // Grid dibawah ini memang kosong karna blm tersedia di LNJ3 jadi index 3 di lewati
                    $detailServiceFromSupplier = [];

                    if ($requestBody['detail'][0]['principal_commodity']) {
                        $count = 0;
                        foreach ($requestBody['detail'][0]['principal_commodity'] as $item) {
                            $ids = $newPrincipalCommodityIds[$count];
                            $count += 1;
                            $crossId = $this->modelName($actionsToModel['updatePrincipalCommodity'])::where('id', $ids)->pluck('ref_id')->first();
                            $detailComodities[] = [
                                'name' => $item['name'] ?? '-',
                                'imo' => $item['imo'] ?? '-',
                                'un' => $item['un'] ?? '-',
                                'pck_grp' => $item['pck_grp'] ?? '-',
                                'fi_pt' => $item['fi_pt'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                            ];
                        }
                    }

                    if ($requestBody['detail'][1]['principal_pic']) {

                        $count = 0;
                        foreach ($requestBody['detail'][1]['principal_pic'] as $item) {
                            $ids = $newPicIds[$count];
                            $count += 1;
                            $crossId = $this->modelName($actionsToModel['updatePrincipalPic'])::where('id', $ids)->pluck('ref_id')->first();
                            $detailPics[] = [
                                'name' => $item['name'] ?? '-',
                                'phone' => $item['phone'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                            ];
                        }
                    }

                    if ($requestBody['detail'][2]['principal_category']) {
                        $count = 0;
                        foreach ($requestBody['detail'][2]['principal_category'] as $item) {
                            $ids = $newCategoryIds[$count];
                            $count += 1;
                            $crossId = $this->modelName($actionsToModel['updatePrincipalCategoryDetail'])::where('id', $ids)->pluck('ref_id')->first();
                            $detailCategories[] = [
                                'principal_category_id' => $item['principal_category_id'],
                                'remark' => $item['remark'] ?? '-',
                                'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                            ];
                        }
                    }

                    if ($requestBody['detail'][4]['address']) {
                        $count = 0;
                        foreach ($requestBody['detail'][4]['address'] as $item) {
                            $ids = $newAddressIds[$count];
                            $count += 1;
                            $crossId = $this->modelName($actionsToModel['updatePrincipalAddress'])::where('id', $ids)->pluck('ref_id')->first();
                            $detailAddresses[] = [
                                'address' => $item['address']  ?? '-',
                                'pic' => $item['pic']  ?? '-',
                                'phone' => $item['phone']  ?? '-',
                                'email' => $item['email']  ?? '-',
                                'contact' => $item['contact'] ?? '-',
                                'remark' => $item['remark'] ?? '-',
                                'note' => $item['note'] ?? '-',
                                'is_visible' => $item['is_visible'],
                                'district_id' => $item['district_id'],
                                'ref_id' => $ids ?? 0, // id dari lnj 3 (bisa id lama kalau update grid yang sama, atau id baru jika insert grid baru), kalau id lama maka di lnj 2 update sesuai id lama, kalau id baru maka insert baru dengan ref id = id baru, selain itu status_aktif = 0
                                'cross_id' => $crossId // cross id ini yang nanti digunakan untuk update sesuai ref_id pada lnj 2, kalau ada ref id maka update nomor di lnj 2 berdasarkan ref id, kalau null maka akan liat id diatas
                            ];
                        }
                    }

                    $detailArray = [
                        'principal_commodity' => $detailComodities,
                        'principal_pic' => $detailPics,
                        'principal_category' => $detailCategories,
                        'service_from_supplier' => $detailServiceFromSupplier,
                        'address' => $detailAddresses,

                    ];
                    $objPayload['detail'] = [$detailArray];
                } else if ($action == 'updatePrincipalGroup') {

                    $details = [];
                    if (!empty($requestBody['detail'][0]['principals'])) {
                        foreach ($requestBody['detail'][0]['principals'] as $detail) {
                            // $principalIds[] = $detail['principal_id'];
                            $crossId = $this->modelName($actionsToModel['updatePrincipal'])::where('id', $detail['principal_id'])->pluck('ref_id')->first();
                            $details[] = [
                                'id_from_lnj3' => $detail['principal_id'],
                                'id_from_lnj2' => $crossId,
                            ];
                        }
                    }

                    $detailPrincipalIds = [
                        'principals' => $details,
                    ];
                    $objPayload['detail'] = [$detailPrincipalIds];
                } else if ($action == 'updateUser') {
                    // update grid
                    $accessBranchDetail = [];
                    $accessBranchReportDetail = [];
                    $accessApplicationDetail = [];
                    $accessFilePrincipalDetail = [];
                    $accessFilePrincipalGroupDetail = [];
                    $accessWebbookingPrincipalGroupDetail = [];
                    $accessDocDistDetail = [];


                    if (!empty($requestBody['detail'])) {
                        $detailPayload = $requestBody['detail'];

                        if (!empty($detailPayload[0]['user_branch'])) {

                            foreach ($detailPayload[0]['user_branch'] as $item) {
                                if (isset($item['branch_id'])) {
                                    $accessBranchDetail[] = [
                                        'branch_id' => $item['branch_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }

                        if (!empty($detailPayload[1]['user_branch_report'])) {

                            foreach ($detailPayload[1]['user_branch_report'] as $item) {
                                if (isset($item['branch_id'])) {
                                    $accessBranchReportDetail[] = [
                                        'branch_id' => $item['branch_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }

                        if (!empty($detailPayload[4]['access_application'])) {

                            foreach ($detailPayload[4]['access_application'] as $item) {
                                if (isset($item['webstite_access_id'])) {
                                    $accessApplicationDetail[] = [
                                        'webstite_access_id' => $item['webstite_access_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }

                        if (!empty($detailPayload[5]['access_file_principal'])) {

                            foreach ($detailPayload[5]['access_file_principal'] as $item) {
                                if (isset($item['principal_file_access_id'])) {
                                    $accessFilePrincipalDetail[] = [
                                        'principal_file_access_id' => $item['principal_file_access_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }

                        if (!empty($detailPayload[6]['access_file_principal_group'])) {

                            foreach ($detailPayload[6]['access_file_principal_group'] as $item) {
                                if (isset($item['principal_group_access_file_id'])) {
                                    $accessFilePrincipalGroupDetail[] = [
                                        'principal_group_access_file_id' => $item['principal_group_access_file_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }

                        if (!empty($detailPayload[7]['access_webbooking_principal_group'])) {

                            foreach ($detailPayload[7]['access_webbooking_principal_group'] as $item) {
                                if (isset($item['principal_group_web_access_id'])) {
                                    $accessWebbookingPrincipalGroupDetail[] = [
                                        'principal_group_web_access_id' => $item['principal_group_web_access_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }

                        if (!empty($detailPayload[8]['access_doc_dist'])) {

                            foreach ($detailPayload[8]['access_doc_dist'] as $item) {
                                if (isset($item['doc_access_id'])) {
                                    $accessDocDistDetail[] = [
                                        'doc_access_id' => $item['doc_access_id'],
                                        'remark' => $item['remark'],
                                    ];
                                }
                            }
                        }


                        $detailBranchArray = [
                            'user_branch' => $accessBranchDetail,
                            'user_branch_report' => $accessBranchReportDetail,
                            'access_application' => $accessApplicationDetail,
                            'access_file_principal' => $accessFilePrincipalDetail,
                            'access_file_principal_group' => $accessFilePrincipalGroupDetail,
                            'access_webbooking_principal_group' => $accessWebbookingPrincipalGroupDetail,
                            'access_doc_dist' => $accessDocDistDetail,
                        ];

                        $objPayload['detail'] = [$detailBranchArray];
                    }
                }

                try {
                    // TODO: di response sini kasih rollback jika gagal
                    // TODO: coba : paksa eror di sync nya
                    $baseSyncUrl = env('SYNC_BASE_URL');
                    $model =  lcfirst($actionsToModel[$action]);
                    if ($action == 'updateStatusQuotation') {
                        $response = Http::put($baseSyncUrl . 'toLNJIS2/master/approve/' . $model . '/' . $id, $objPayload);
                    } else {
                        $response = Http::put($baseSyncUrl . 'toLNJIS2/master/update/' . $model . '/' . $id, $objPayload);
                    }
                    if ($response) {
                        $timestamp = date('Y-m-d H:i:s');
                        $logEntry = "$timestamp - $response\n";
                        File::append(storage_path('logs/error.log'), $logEntry);
                    }
                } catch (\Exception $e) {
                    $timestamp = date('Y-m-d H:i:s');
                    $errorMessage = $e->getMessage();
                    $logEntry = "$timestamp - $errorMessage\n";
                    File::append(storage_path('logs/error.log'), $logEntry);
                }
            }
        }

        if (!$data) {
            return $this->sendResponse(
                false,
                Response::HTTP_BAD_REQUEST,
                $data
            );
        }
        
        return $this->sendResponse(
            true,
            Response::HTTP_OK,
            $data
        );
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Request $request, $id)
    {
        $actionsToModel = $this->globalActionController->getActionsToModel();
        // get action in request
        $action = $request->action;
        // get request body
        $requestBody = $request->requestData;

        if (!array_key_exists($action, $actionsToModel)) {
            return $this->sendResponse(false, Response::HTTP_NOT_FOUND, 'Action Not Found!');
        }


        if (!isset($requestBody['is_lnj2'])) {

            // jika terdapat ref_id maka kirimkan ke sync
            $deletedData = $this->modelName($actionsToModel[$action])::where('id', $id)->first();
            if ($deletedData !== null) {
                $refId = $deletedData->ref_id;
            } else {
                // Handle the case where $deletedData is null (optional, depending on your needs)
                $refId = null;
            }

            $data = $this->modelName($actionsToModel[$action])::where('id', $id)->update([
                'deleted_by' => auth()->id(),
                'is_active' => false, // nonaktifkan jika di delete
            ]);

            $data = $this->modelName($actionsToModel[$action])::where('id', $id)->delete();

            // send data to LNJ V 2
            if ($data) {
                $objPayload = [
                    'data' => $data,
                    'requestData' => ['ref_id' => $refId]
                ];

                if ($refId == null) {
                    $refId = $id;
                }
                try {
                    // TODO: di response sini kasih rollback jika gagal
                    // TODO: coba : paksa eror di sync nya
                    $baseSyncUrl = env('SYNC_BASE_URL');
                    $model =  lcfirst($actionsToModel[$action]);
                    $response = Http::delete($baseSyncUrl . 'toLNJIS2/master/delete/' . $model . '/' . $refId, $objPayload);
                    $timestamp = date('Y-m-d H:i:s');
                    $logEntry = "$timestamp - $response\n";
                    File::append(storage_path('logs/laravel.log'), $logEntry);
                } catch (\Exception $e) {
                    $timestamp = date('Y-m-d H:i:s');
                    $errorMessage = $e->getMessage();
                    $logEntry = "$timestamp - $errorMessage\n";
                    File::append(storage_path('logs/error.log'), $logEntry);
                    return false;
                }
            }
        } else {
            if (isset($requestBody['ref_id_true'])) {
                $data = $this->modelName($actionsToModel[$action])::where('id', $id)->delete();
            } else {
                $data = $this->modelName($actionsToModel[$action])::where('ref_id', $id)->delete();
            }
        }

        return $this->sendResponse(
            true,
            Response::HTTP_OK,
            $data
        );
    }
}
