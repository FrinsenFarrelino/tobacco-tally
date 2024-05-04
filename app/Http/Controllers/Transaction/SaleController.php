<?php

namespace App\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class SaleController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    private $index_file;
    private $form_file;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal(module: 'transaction', menuParam: 'sale', subModule: 'transaction_sale', menuRoute: 'sale', menuUrl: 'transaction/sale');

        $this->index_file = 'transaction.sale.index';
        $this->form_file = 'transaction.sale.form';
    }

    private function computeSetFeatures()
    {
        // You can use the existing logic you have in setPrivButton or modify it as needed
        $code = 'sale';
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
        $formData['action'] = $this->globalVariable->actionGetSale;
        $formData['menu_route'] = $this->globalVariable->menuRoute;
        $formData['menu_param'] = $this->globalVariable->menuParam;

        return view($this->index_file,$formData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $generate_nav_button = generateNavbutton([], 'back|save', 'save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'add');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['action_customer'] = $this->globalVariable->actionGetCustomer;
        $today = Carbon::today();
        $formData['today'] = Carbon::parse($today)->toDateString();
        $formData['ppn'] = $this->getDataTax();

        return view($this->form_file, $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $make_detail = $request->input('detail');
        $detailArray = json_decode($make_detail, true);

        $request->merge(['detail' => $detailArray]);

        $set_request = SetRequestGlobal('addSale', $request, formatCode: 'code_sale');
        $result = $this->addData($set_request);

        if ($result['success'] == false) {
            return redirect()->back()
                ->withErrors($result['errors'])
                ->with('message', $result['message'])
                ->with('status_code', $result['status_code'])
                ->withInput();
        }

        session()->flash('success', 'Add operation was successful.');

        return redirect('/' . $this->globalVariable->menuUrl);
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
            'key' => 'sales.id',
            'term' => 'equal',
            'query' => $getId
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetSale, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back|approve|disapprove|print' . $setFeatures, 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['transaction_sale'] = $decodedData;
        $formData['action_customer'] = $this->globalVariable->actionGetCustomer;
        $today = Carbon::today();
        $formData['today'] = Carbon::parse($today)->toDateString();
        $formData['show_button'] = $show_button;
        $formData['ppn'] = $this->getDataTax();

        return view($this->form_file, $formData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $search_key[] = array(
            'key' => 'sales.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetSale, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $generate_nav_button = generateNavbutton($decodedData, 'back|save', 'edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['transaction_sale'] = $decodedData;
        $formData['action_customer'] = $this->globalVariable->actionGetCustomer;
        $today = Carbon::today();
        $formData['today'] = Carbon::parse($today)->toDateString();
        $formData['ppn'] = $this->getDataTax();

        return view($this->form_file, $formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $make_detail = $request->input('detail');
        $detailArray = json_decode($make_detail, true);

        $request->merge(['detail' => $detailArray]);
        $set_request = SetRequestGlobal('updateSale', $request);
        $result = $this->updateData($set_request, $id);

        if ($result['success'] == false) {
            return redirect()->back()
                ->withErrors($result['errors'])
                ->with('message', $result['message'])
                ->with('status_code', $result['status_code'])
                ->withInput();
        }

        session()->flash('success', 'Update operation was successful.');

        return redirect('/' . $this->globalVariable->menuUrl);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $set_request = SetRequestGlobal('softDeleteSale');
        $result = $this->softDeleteData($set_request, $id);

        if ($result['success'] == false) {
            return redirect()->back()
                ->withErrors($result['errors'])
                ->with('message', $result['message'])
                ->with('status_code', $result['status_code'])
                ->withInput();
        }

        session()->flash('success', 'Delete operation was successful.');

        return redirect('/' . $this->globalVariable->menuUrl);
    }

    public function updateStatus(Request $request, string $id)
    {
        $set_request = SetRequestGlobal('updateStatusSale', $request);
        $result = $this->updateData($set_request, $id);

        return $result;
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
