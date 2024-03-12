<?php

namespace App\Http\Controllers\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalVariable;
use Illuminate\Support\Facades\Http;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Events\LogoutOtherUsers;
use App\Traits\ValidationTrait;

class GroupUserController extends Controller
{
    private $globalVariable;

    private $index_file;

    private $form_file;

    private $select_category;

    private $arrayIsActive;

    public function __construct(GlobalVariable $globalVariable)
    {
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal('setting', 'group_user', 'setting/group-user', 'group-user', 'group_user');

        $this->arrayIsActive = array(['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Inactive']);

        $this->index_file = 'setting.group_user.index';
        $this->form_file = 'setting.group_user.form';
    }

    private function getSelectCategory(){
        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetEnumUserGroupData, deviceInfo:collectDeviceInfo());
        $result = $this->getApi($set_request);
        $array_category = [];

        if($result['success'] == false || $result['success'] == null)
        {
            $array_category = array(['id'=>'template','name'=>'Template'],['id'=>'admin','name'=>'Admin'],['id'=>'webbooking','name'=>'Webbooking']);
        }
        else
        {
            foreach ($result['data'] as $value) {
                array_push($array_category,array('id'=>$value['enumlabel'],'name'=>ucwords($value['enumlabel'])));
            }
        }
        $this->select_category = $array_category;
    }

    private function computeSetFeatures()
    {
        // You can use the existing logic you have in setPrivButton or modify it as needed

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
        $formData['setFeatures'] = $setFeatures;

        return view($this->index_file, $formData);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetUserGroup, deviceInfo:collectDeviceInfo(), filter:array('id' => $id));
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $setFeatures = $this->computeSetFeatures();

        $this->getSelectCategory();

        $generate_nav_button = generateNavbutton($decodedData,'back'.$setFeatures,'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['category'] = $this->select_category;
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['group_user'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['action_group_user'] = $this->globalVariable->actionGetUserGroup;

        return view($this->form_file, $formData);
    }

    /**
     * Display the specified resource.
     */
    public function showMenuPermission(string $id, Request $request)
    {
        $set_request = SetRequestGlobal(action:'getAccessMenu', deviceInfo:collectDeviceInfo(), search:[array('key' => 'access_menus.user_group_id', 'term' => 'equal', 'query' => $id)]);
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $generate_nav_button = generateNavbutton([],'back|save','save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, 'setting/group-user/menu-permission', 'index');
        $formData['access_menu'] = $result['data']['data'];
        $formData['list_nav_button'] = $generate_nav_button;

        return view('setting.group_user.menu-permission',$formData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $generate_nav_button = generateNavbutton([],'back|save','save', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'add');

        $this->getSelectCategory();

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['category'] = $this->select_category;
        $formData['action_group_user'] = $this->globalVariable->actionGetUserGroup;

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

        $set_request = SetRequestGlobal('addUserGroup', collectDeviceInfo(), $request, array('created_at'=>'created_at'));

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

        return redirect('/'.$this->globalVariable->menuUrl);
    }

    /**
     * Store or Update a newly access menu permission in storage.
     */
    public function saveAccessMenuPermission(Request $request) {
        $setDataPrivilage = [];
        $authToken = Session::get('auth_token');
        $groupUserId = $request->input('user_group_id');

        foreach (Session::get('list_menu') as $value) {
            $setID = $value['id'];
            foreach ($request->input('menus') as $menuId => $menuData) {
                // $menuId will be the menu ID
                // $menuData will be an array with the checkbox values

                // Example: Check if 'open' is checked for this menu item
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
                    $rejectChecked = isset($menuData['reject']) && $menuData['reject'] === 'on';
                    $finishChecked = isset($menuData['close']) && $menuData['close'] === 'on';
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
                    $rejectChecked = false;
                    $finishChecked = false;
                }
            }
            $setDataPrivilage[] = array('user_group_id' => $groupUserId, 'menu_id' => $setID, 'open' => $openChecked, 'add' => $addChecked, 'edit' => $editChecked, 'delete' => $deleteChecked, 'print' => $printChecked, 'approve' => $approveChecked, 'disapprove' => $disapproveChecked, 'reject' => $rejectChecked, 'close' => $finishChecked);
        }

        $resSetPrivilage = [
            'priviledges' => $setDataPrivilage,
        ];

        $response = Http::accept('application/json')->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->post($this->getUrlBase('user-group/set-priviledge/' . $groupUserId),
            $resSetPrivilage,
        );

        $result = json_decode($response,true);

        if($result['success'] == false)
        {
            return redirect()->back()
                    ->withErrors($result['errors'])
                    ->with('message', $result['message'])
                    ->with('status_code', $result['status_code'])
                    ->withInput();
            // return back()->withErrors(['message'=>$result['message']])->withInput();
        }

        //find user id from api
        $set_request_find_user = SetRequestGlobal(action:$this->globalVariable->actionGetUser, deviceInfo:collectDeviceInfo(), search:[array('key' => 'user_group_id', 'term' => 'equal', 'query' => $groupUserId)]);
        $result_find_user = $this->getApi($set_request_find_user);

        // $userNow = Session::get('user');

        if($result_find_user['data']['recordsTotal'] > 0)
        {
            foreach ($result_find_user['data']['data'] as $value) {
                // if($value['id'] == $userNow['id'])
                // {
                //     //minta buat api get access master
                //     $set_request_group_user = SetRequestGlobal(action:'getAccessMenu', deviceInfo:collectDeviceInfo(), search:[array('key' => 'user_group_id', 'term' => 'equal', 'query' => $groupUserId)]);
                //     $result_group_user = $this->getApi($set_request_group_user);
                //     Session::put('access_menu',$result_group_user['data']['data']);
                // }
                event(new LogoutOtherUsers($value['id']));
            }
        }

        session()->flash('success', 'Edit operation was successful.');

        return redirect('/'.$this->globalVariable->menuUrl);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetUserGroup, deviceInfo:collectDeviceInfo(), filter:array('id' => $id));
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $generate_nav_button = generateNavbutton($decodedData,'back|save','edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $this->getSelectCategory();

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['category'] = $this->select_category;
        $formData['group_user'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['action_group_user'] = $this->globalVariable->actionGetUserGroup;

        return view($this->form_file, $formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $validationResponse = $this->handleValidation($request, 'update');

        if ($validationResponse) {
            return $validationResponse;
        }

        $set_request = SetRequestGlobal('updateUserGroup',collectDeviceInfo(),$request);

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

        return redirect('/'.$this->globalVariable->menuUrl);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $set_request = SetRequestGlobal('softDeleteUserGroup',collectDeviceInfo());

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

        return redirect('/'.$this->globalVariable->menuUrl);
    }
}
