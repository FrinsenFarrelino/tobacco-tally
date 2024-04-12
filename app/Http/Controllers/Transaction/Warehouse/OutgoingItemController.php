<?php

namespace App\Http\Controllers\Transaction\Warehouse;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class OutgoingItemController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    private $index_file;
    private $form_file;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal(module: 'transaction', menuParam: 'outgoing_item', subModule: 'transaction_warehouse_outgoing_item', menuRoute: 'outgoing-item', menuUrl: 'transaction/warehouse/outgoing-item');

        $this->index_file = 'transaction.warehouse.outgoing_item.index';
        $this->form_file = 'transaction.warehouse.outgoing_item.form';
    }

    private function computeSetFeatures()
    {
        // You can use the existing logic you have in setPrivButton or modify it as needed
        $code = 'outgoing_item';
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
        $formData['action'] = $this->globalVariable->actionGetOutgoingItem;
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
        $today = Carbon::today();
        $formData['today'] = Carbon::parse($today)->toDateString();

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

        $set_request = SetRequestGlobal('addOutgoingItem', $request, formatCode: 'code_stock_transfer');
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
            'key' => 'stock_transfers.id',
            'term' => 'equal',
            'query' => $getId
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetOutgoingItem, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back|approve|disapprove|print' . $setFeatures, 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['transaction_warehouse_outgoing_item'] = $decodedData;
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
        $search_key[] = array(
            'key' => 'stock_transfers.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetOutgoingItem, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $generate_nav_button = generateNavbutton($decodedData, 'back|save', 'edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['transaction_warehouse_outgoing_item'] = $decodedData;
        $today = Carbon::today();
        $formData['today'] = Carbon::parse($today)->toDateString();

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
        $set_request = SetRequestGlobal('updateOutgoingItem', $request);
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
        $set_request = SetRequestGlobal('softDeleteOutgoingItem');
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
        $set_request = SetRequestGlobal('updateStatusOutgoingItem', $request);
        $result = $this->updateData($set_request, $id);

        return $result;
    }
}
