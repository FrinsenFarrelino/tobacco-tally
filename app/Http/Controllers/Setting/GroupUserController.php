<?php

namespace App\Http\Controllers\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalVariable;
use Illuminate\Support\Facades\Http;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Events\LogoutOtherUsers;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use Illuminate\Support\Facades\Session as FacadesSession;

class GroupUserController extends GlobalController
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
        $this->globalVariable->ModuleGlobal(module: 'setting', menuParam: 'group_user', subModule: 'group_user', menuRoute: 'group-user', menuUrl: 'setting/group-user');

        $this->index_file = 'setting.group_user.index';
        $this->form_file = 'setting.group_user.form';

        $this->arrayIsActive = array(['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Inactive']);
    }

    private function computeSetFeatures()
    {
        $code = 'group_user';
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

        $formData['action'] = $this->globalVariable->actionGetUserGroup;
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['menu_route'] = $this->globalVariable->menuRoute;
        $formData['menu_param'] = $this->globalVariable->menuParam;

        return view($this->index_file, $formData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $generate_nav_button = generateNavbutton([],'back|save','save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'add');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $set_request = SetRequestGlobal('addUserGroup', $request);
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
            'key' => 'user_groups.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetUserGroup, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back' . $setFeatures, 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['setting_group_user'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $search_key[] = array(
            'key' => 'user_groups.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetUserGroup, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $generate_nav_button = generateNavbutton($decodedData, 'back|save', 'edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['setting_group_user'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $set_request = SetRequestGlobal('updateUserGroup', $request);
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
    public function destroy($id)
    {
        $set_request = SetRequestGlobal('softDeleteUserGroup');
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

    public function showAccessMenu(string $id, Request $request)
    {
        $search_key[] = array(
            'key' => 'access_menus.user_group_id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: 'getAccessMenu', search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'];

        $generate_nav_button = generateNavbutton([],'back|save','save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);
        
        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, 'setting/group-user/menu-permission', 'index');
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['access_menu'] = $decodedData;

        return view('setting.group_user.access-menu', $formData);
    }

    public function setAccessMenu(Request $request) {
        $setDataPrivilage = [];
        $groupUserId = $request->input('user_group_id');

        foreach (FacadesSession::get('list_menu') as $value) {
            $setID = $value['id'];
            foreach ($request->input('menus') as $menuId => $menuData) {
                if($menuId == $setID)
                {
                    $setID = $menuId;
                    $openChecked = isset($menuData['open']) && $menuData['open'] === 'on';
                    $addChecked = isset($menuData['add']) && $menuData['add'] === 'on';
                    $editChecked = isset($menuData['edit']) && $menuData['edit'] === 'on';
                    $deleteChecked = isset($menuData['delete']) && $menuData['delete'] === 'on';
                    $printChecked = isset($menuData['print']) && $menuData['print'] === 'on';
                    $approveChecked = isset($menuData['approve']) && $menuData['approve'] === 'on';
                    $disapproveChecked = isset($menuData['disapprove']) && $menuData['disapprove'] === 'on';
                    break;
                }
                else
                {
                    $openChecked = false;
                    $addChecked = false;
                    $editChecked = false;
                    $deleteChecked = false;
                    $printChecked = false;
                    $approveChecked = false;
                    $disapproveChecked = false;
                }
            }
            $setDataPrivilage[] = array('user_group_id' => $groupUserId, 'menu_id' => $setID, 'open' => $openChecked, 'add' => $addChecked, 'edit' => $editChecked, 'delete' => $deleteChecked, 'print' => $printChecked, 'approve' => $approveChecked, 'disapprove' => $disapproveChecked);
        }

        $resSetPrivilage = [
            'priviledges' => $setDataPrivilage,
        ];

        
        $result = $this->setPriviledge($resSetPrivilage, $groupUserId);

        if($result['success'] == false)
        {
            return redirect()->back()
                    ->withErrors($result['errors'])
                    ->with('message', $result['message'])
                    ->with('status_code', $result['status_code'])
                    ->withInput();
        }
        session()->flash('success', 'Edit operation was successful.');

        return redirect('/'.$this->globalVariable->menuUrl);
    }
}
