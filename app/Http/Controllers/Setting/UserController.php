<?php

namespace App\Http\Controllers\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\GlobalVariable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends GlobalController
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
        $this->globalVariable->ModuleGlobal(module: 'setting', menuParam: 'user', subModule: 'setting_user', menuRoute: 'user', menuUrl: 'setting/user');

        $this->index_file = 'setting.user.index';
        $this->form_file = 'setting.user.form';
        $this->arrayIsActive = array(['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Inactive']);
    }

    private function computeSetFeatures()
    {
        // You can use the existing logic you have in setPrivButton or modify it as needed
        $code = 'user';
        $setValueFeature = $this->setPrivButton($code);

        return $setValueFeature;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton([],'reload'.$setFeatures, 'index', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'index');

        $formData['action'] = $this->globalVariable->actionGetUser;
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
        $formData['action_user_group'] = $this->globalVariable->actionGetUserGroup;
        $formData['action_employee'] = $this->globalVariable->actionGetEmployee;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'name' => 'required',
        ]);

        $set_request = SetRequestGlobal('addUser', $request);
        $result = $this->addData($set_request);

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
            'key' => 'users.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetUser, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $setFeatures = $this->computeSetFeatures();
        $generate_nav_button = generateNavbutton($decodedData, 'back' . $setFeatures, 'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['setting_user'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['action_user_group'] = $this->globalVariable->actionGetUserGroup;
        $formData['action_employee'] = $this->globalVariable->actionGetEmployee;

        return view($this->form_file, $formData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $search_key[] = array(
            'key' => 'users.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetUser, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $generate_nav_button = generateNavbutton($decodedData, 'back|save', 'edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['setting_user'] = $decodedData;
        $formData['selectActive'] = $this->arrayIsActive;
        $formData['action_user_group'] = $this->globalVariable->actionGetUserGroup;
        $formData['action_employee'] = $this->globalVariable->actionGetEmployee;

        return view($this->form_file, $formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'username' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'name' => 'required',
        ];

        // Check if password field is filled, then validate the password confirmation
        if ($request->filled('password')) {
            $rules['password'] = 'required|confirmed|min:8';
        } else {
            unset($request['password']);
            unset($request['password_confirmation']);
        }

        // Validate the request
        $this->validate($request, $rules);

        $set_request = SetRequestGlobal('updateUser', $request);
        $result = $this->updateData($set_request, $id);

        if($result['success'] == false)
        {
            return redirect()->back()
                    ->withErrors($result['errors'])
                    ->with('message', $result['message'])
                    ->with('status_code', $result['status_code'])
                    ->withInput();
        }

        elseif($result == null)
        {
            session()->flash('success', 'Update operation was successful.');
            return redirect('/'. $this->globalVariable->menuUrl);
        }

        session()->flash('success', 'Update operation was successful.');
        return redirect('/'. $this->globalVariable->menuUrl);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $set_request = SetRequestGlobal('softDeleteUser');
        $result = $this->softDeleteData($set_request, $id);

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

    public function seeProfile()
    {
        $id = auth()->user()->id;

        $search_key[] = array(
            'key' => 'users.id',
            'term' => 'equal',
            'query' => $id
        );

        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetUser, search: $search_key);
        $result = $this->getData($set_request);
        $decodedData = $result['data'][0];

        $generate_nav_button = generateNavbutton($decodedData, 'save', 'edit', '', 'dashboard', 'profile');
        $formData = $this->objResponse('dashboard', 'profile_user', 'profile-user', 'index');
        $formData['list_nav_button'] = $generate_nav_button;
        $formData['user'] = $decodedData;

        return view('profile-user',$formData);
    }

    public function updateProfileSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $user = $request->user();
    
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect'])
                ->withInput();
        }
    
        $user->update([
            'password' => Hash::make($request->password),
        ]);
    
        return redirect()->back()->with('success', 'Update Password Success');
    }
}
