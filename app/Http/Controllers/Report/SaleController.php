<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    private $index_file;
    private $form_file;
    private $print_file;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal(module: 'report', menuParam: 'sale_report', subModule: 'transaction_sale', menuRoute: 'sale-report', menuUrl: 'report/sale-report');

        $this->index_file = 'report.sale.index';
        $this->form_file = 'report.sale.form';
        $this->print_file = 'report.sale.print';
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
            'key' => 'sales.id',
            'term' => 'equal',
            'query' => $getId
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetSale, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back|print', 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['transaction_sale'] = $decodedData;
        $formData['action_customer'] = $this->globalVariable->actionGetCustomer;
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
        // retreive all records from db
        $search_key[] = array(
            'key' => 'sales.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetSale, search:$search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $search_key_detail[] = array(
            'key' => 'sale_item_details.sale_id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request_grid = SetRequestGlobal(action:'getSaleDetail', search:$search_key_detail);
        $result_grid = $this->getData($set_request_grid);
        $decodedDataGrid = $result_grid['data'];

        $formData['data'] = $decodedData;
        $formData['data_grid'] = $decodedDataGrid;

        // load blade / html content to pdf
        $pdf = PDF::loadView($this->print_file, $formData)->setPaper('A4','landscape');

        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();

        // another way to define footer. but if there using page then use this.
        $canvas->page_text($canvas->get_width() - 60, $canvas->get_height() - 42, "Hal {PAGE_NUM} / {PAGE_COUNT}", null, 11, [0, 0, 0]);
        // $canvas->page_text(180, $canvas->get_height() - 42, "    Hal {PAGE_NUM} / {PAGE_COUNT}", null, 11, [0, 0, 0]);

        return $pdf->stream();

    }
}
