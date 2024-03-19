<?php

namespace App\Http\Controllers\MasterData\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalVariable;
use App\Traits\ValidationTrait;

class CityController extends Controller
{
    private $globalVariable;

    private $index_file;
    private $form_file;

    public function __construct(GlobalVariable $globalVariable)
    {
        $this->globalVariable = $globalVariable;

        $this->globalVariable->ModuleGlobal("master_data", "branch_office", 'master-data/branch-office', 'branch-office', 'branch_office');

        $this->index_file = 'master_data.branch_office.index';
        $this->form_file = 'master_data.branch_office.form';
    }

    private function computeSetFeatures()
    {
        $code = 'branch';
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
        $formData['action'] = $this->globalVariable->actionGetBranchOffice;
        $formData['menu_route'] = $this->globalVariable->menuRoute;
        $formData['menu_param'] = $this->globalVariable->menuParam;
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['setFeatures'] = $setFeatures;
        return view($this->index_file,$formData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $generate_nav_button = generateNavbutton([],'back|save','save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'add');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['is_center'] = $this->arrayIsCenter;
        $formData['action_port'] = $this->globalVariable->actionGetPort;
        $formData['action_pic'] = $this->globalVariable->actionGetEmployee;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    use ValidationTrait;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validationResponse = $this->handleValidation($request, 'add');

        if ($validationResponse) {
            return $validationResponse;
        }

        $set_request = SetRequestGlobal('addBranch', collectDeviceInfo(), $request, array('created_at'=>'created_at'), manualCode: $request->code);

        $result = $this->sendApi($set_request, 'post');

        if($result['success'] == false)
        {
            return redirect()->back()
                    ->withErrors($result['errors'])
                    ->with('message', $result['message'])
                    ->with('status_code', $result['status_code'])
                    ->withInput();
        }

        session()->flash('success', 'Add operation was successful.');

        return redirect('/'. $this->globalVariable->menuUrl);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $search_key[] = array(
            'key' => 'branches.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetBranchOffice, deviceInfo:collectDeviceInfo(), search:$search_key);
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData,'back'.$setFeatures,'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['branch'] = $decodedData;
        $formData['is_center'] = $this->arrayIsCenter;
        $formData['action_port'] = $this->globalVariable->actionGetPort;
        $formData['action_pic'] = $this->globalVariable->actionGetEmployee;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file,$formData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $search_key[] = array(
            'key' => 'branches.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetBranchOffice, deviceInfo:collectDeviceInfo(), search:$search_key);
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $generate_nav_button = generateNavbutton($decodedData,'back|save','edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['branch'] = $decodedData;
        $formData['is_center'] = $this->arrayIsCenter;
        $formData['action_port'] = $this->globalVariable->actionGetPort;
        $formData['action_pic'] = $this->globalVariable->actionGetEmployee;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file,$formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validationResponse = $this->handleValidation($request, 'update');

        if ($validationResponse) {
            return $validationResponse;
        }

        $set_request = SetRequestGlobal('updateBranch',collectDeviceInfo(),$request);

        $result = $this->sendApi($set_request, 'put',$id);

        if($result['success'] == false)
        {
            return redirect()->back()
                    ->withErrors($result['errors'])
                    ->with('message', $result['message'])
                    ->with('status_code', $result['status_code'])
                    ->withInput();
        }

        session()->flash('success', 'Update operation was successful.');

        return redirect('/'. $this->globalVariable->menuUrl);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $set_request = SetRequestGlobal('softDeleteBranch',collectDeviceInfo());

        $result = $this->sendApi($set_request, 'delete', $id);

        if($result['success'] == false)
        {
            return redirect()->back()
                    ->withErrors($result['errors'])
                    ->with('message', $result['message'])
                    ->with('status_code', $result['status_code'])
                    ->withInput();
        }

        session()->flash('success', 'Delete operation was successful.');

        return redirect('/'. $this->globalVariable->menuUrl);
    }
}
