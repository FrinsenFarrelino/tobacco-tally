<?php

namespace App\Http\Controllers\Setting\Tax;

use Illuminate\Http\Request;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GlobalVariable;
use App\Models\Tax;

class TaxSettingController extends GlobalController
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
        $this->globalVariable->ModuleGlobal(module: 'setting', menuParam: 'tax_setting', subModule: 'setting_tax_tax_setting', menuRoute: 'tax-setting', menuUrl: 'setting/tax/tax-setting');

        $this->index_file = 'setting.tax.tax_setting.form';
    }

    private function computeSetFeatures()
    {
        $code = 'province';
        $setValueFeature = $this->setPrivButton($code);

        return $setValueFeature;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetTax, type: 'latest');
        $result = $this->getData($set_request);
        if ($result['data'] !== null) {
            $decodedData = $result['data'];
        } else {
            $decodedData = new Tax([
                'ppn' => 0
            ]);
        }
        
        $generate_nav_button = generateNavbutton([], 'save', 'save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);
        
        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');
        
        $formData['setting_tax_tax_setting'] = $decodedData;
        $formData['list_nav_button'] = $generate_nav_button;

        return view($this->index_file, $formData);
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
        $set_request = SetRequestGlobal('updateTax', $request);
        $result = $this->updateData($set_request, 1);


        if ($result['success'] == false) {
            return redirect()->back()->withErrors($result['message'])->withInput();
        }

        return redirect()->back()->with('success', 'Setting PPN success');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
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
}
