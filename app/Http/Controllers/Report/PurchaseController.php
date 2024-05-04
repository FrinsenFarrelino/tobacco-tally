<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class PurchaseController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    private $index_file;
    private $form_file;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal(module: 'transaction', menuParam: 'purchase_report', subModule: 'transaction_purchase', menuRoute: 'purchase-report', menuUrl: 'report/purchase-report');

        $this->index_file = 'report.purchase.index';
        $this->form_file = 'report.purchase.form';
    }

    private function computeSetFeatures()
    {
        // You can use the existing logic you have in setPrivButton or modify it as needed
        $code = 'purchase';
        $setValueFeature = $this->setPrivButton($code);

        return $setValueFeature;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setFeatures = $this->computeSetFeatures();

        $generate_nav_button = generateNavbutton([],'reload'.$setFeatures,'index', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'index');
        
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['action'] = $this->globalVariable->actionGetPurchase;
        $formData['menu_route'] = $this->globalVariable->menuRoute;
        $formData['menu_param'] = $this->globalVariable->menuParam;

        return view($this->index_file,$formData);
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
        $getId = $id;
        $show_button = '';
        if(str_contains($id,',')){
            $resultExplode = explode(',',$id);
            $show_button = $resultExplode[1];
            $getId = $resultExplode[0];
        }

        $search_key[] = array(
            'key' => 'purchases.id',
            'term' => 'equal',
            'query' => $getId
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetPurchase, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back|print', 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['transaction_purchase'] = $decodedData;
        $formData['action_supplier'] = $this->globalVariable->actionGetSupplier;
        $today = Carbon::today();
        $formData['today'] = Carbon::parse($today)->toDateString();
        $formData['show_button'] = $show_button;

        return view($this->form_file, $formData);
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

    public function createPDF(string $id) {
        dd($id);
        // // retreive all records from db
        // $search_key[] = array(
        //     'key' => 'marketing_orders.id',
        //     'term' => 'equal',
        //     'query' => $id
        // );

        // $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetMarketingOrder, deviceInfo:collectDeviceInfo(), search:$search_key);
        // $result = $this->getApi($set_request);
        // $decodedData = removeArrayBracket($result['data']['data']);

        // $search_key_detail[] = array(
        //     'key' => 'marketing_order_item_details.marketing_order_id',
        //     'term' => 'equal',
        //     'query' => $id
        // );

        // $set_request_grid = SetRequestGlobal(action:'getMarketingOrderDetail', deviceInfo:collectDeviceInfo(), search:$search_key_detail);

        // $result_grid = $this->getApi($set_request_grid);

        // $formData['data'] = $decodedData;
        // $formData['data_grid'] = $result_grid['data']['data'];

        // // load blade / html content to pdf
        // $pdf = PDF::loadView($this->print_file, $formData)->setPaper('A4','landscape');

        // $pdf->output();
        // $domPdf = $pdf->getDomPDF();
        // $canvas = $domPdf->get_canvas();

        // // another way to define footer. but if there using page then use this.
        // $canvas->page_text($canvas->get_width() - 60, $canvas->get_height() - 42, "Hal {PAGE_NUM} / {PAGE_COUNT}", null, 11, [0, 0, 0]);
        // // $canvas->page_text(180, $canvas->get_height() - 42, "    Hal {PAGE_NUM} / {PAGE_COUNT}", null, 11, [0, 0, 0]);

        // return $pdf->stream();

        // // download PDF file with download method
        // // return $pdf->download('pdf_file.pdf');
    }
}
