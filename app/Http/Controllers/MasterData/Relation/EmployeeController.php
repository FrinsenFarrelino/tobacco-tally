<?php

namespace App\Http\Controllers\MasterData\Relation;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;

class EmployeeController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    private $index_file;
    private $form_file;

    private $arrayIsActive;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal(module: 'master_data', menuParam: 'employee', subModule: 'master_data_relation_employee', menuRoute: 'employee', menuUrl: 'master-data/relation/employee');

        $this->index_file = 'master_data.relation.employee.index';
        $this->form_file = 'master_data.relation.employee.form';

        $this->arrayIsActive = [['id' => '1', 'name' => 'Active'], ['id' => '0', 'name' => 'Inactive']];
    }

    private function computeSetFeatures()
    {
        // You can use the existing logic you have in setPrivButton or modify it as needed

        $code = 'employee';
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
        $formData['action'] = $this->globalVariable->actionGetEmployee;
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
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['action_subdistrict'] = $this->globalVariable->actionGetSubdistrict;
        $formData['action_division'] = $this->globalVariable->actionGetDivision;
        $formData['action_position'] = $this->globalVariable->actionGetPosition;

        return view($this->form_file, $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $set_request = SetRequestGlobal('addEmployee', $request, formatCode: 'code_employee');
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
        $search_key[] = array(
            'key' => 'employees.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetEmployee, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back' . $setFeatures, 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['master_data_relation_employee'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['action_subdistrict'] = $this->globalVariable->actionGetSubdistrict;
        $formData['action_division'] = $this->globalVariable->actionGetDivision;
        $formData['action_position'] = $this->globalVariable->actionGetPosition;

        return view($this->form_file, $formData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $search_key[] = array(
            'key' => 'employees.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetEmployee, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $generate_nav_button = generateNavbutton($decodedData, 'back|save', 'edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['master_data_relation_employee'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['action_subdistrict'] = $this->globalVariable->actionGetSubdistrict;
        $formData['action_division'] = $this->globalVariable->actionGetDivision;
        $formData['action_position'] = $this->globalVariable->actionGetPosition;

        return view($this->form_file, $formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $set_request = SetRequestGlobal('updateEmployee', $request);
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
        $set_request = SetRequestGlobal('softDeleteEmployee');
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
}
