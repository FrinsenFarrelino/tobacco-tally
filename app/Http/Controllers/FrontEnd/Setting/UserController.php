<?php

namespace App\Http\Controllers\FrontEnd\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontEnd\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\GlobalVariable;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use App\Traits\ValidationTrait;

class UserController extends Controller
{
    private $globalVariable;

    private $index_file;

    private $form_file;

    private $is_show_all_tickets;
    private $is_post_cs;
    private $arrayIsActive;

    public function __construct(GlobalVariable $globalVariable)
    {
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal('setting', 'user', 'setting/user', 'user', 'user');

        $this->index_file = 'setting.user.index';
        $this->form_file = 'setting.user.form';
        $this->is_show_all_tickets = generateIsBooleanNo('No','Yes');
        $this->is_post_cs = generateIsBooleanNo('No','Yes');
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
        $formData['setFeatures'] = $setFeatures;

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
        $formData['is_show_all_tickets'] = $this->is_show_all_tickets;
        $formData['is_post_cs'] = $this->is_post_cs;

        return view($this->form_file, $formData);
    }

    use ValidationTrait;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $make_detail = $request->input('detail');
        $detailArray = json_decode($make_detail, true);

        foreach ($detailArray as $key => $value) {
            if (isset($value['user_branch'])) {
                foreach ($value['user_branch'] as $second_key => $data) {
                    if (array_key_exists("branch_name", $data)) {
                        unset($detailArray[$key]['user_branch'][$second_key]['branch_name']);
                    }
                }
            }
            elseif (isset($value['user_branch_report'])) {
                foreach ($value['user_branch_report'] as $second_key => $data) {
                    if (array_key_exists("branch_report_name", $data)) {
                        unset($detailArray[$key]['user_branch_report'][$second_key]['branch_report_name']);
                    }
                }
            }
            elseif (isset($value['access_application'])) {
                foreach ($value['access_application'] as $second_key => $data) {
                    if (array_key_exists("webstite_access_name", $data)) {
                        unset($detailArray[$key]['access_application'][$second_key]['webstite_access_name']);
                    }
                }
            }
            elseif (isset($value['user_company'])) {
                foreach ($value['user_company'] as $second_key => $data) {
                    if (array_key_exists("user_company_name", $data)) {
                        unset($detailArray[$key]['user_company'][$second_key]['user_company_name']);
                    }
                }
            }
            elseif (isset($value['user_group'])) {
                foreach ($value['user_group'] as $second_key => $data) {
                    if (array_key_exists("user_group_name", $data)) {
                        unset($detailArray[$key]['user_group'][$second_key]['user_group_name']);
                    }
                }
            }
            elseif (isset($value['access_file_principal'])) {
                foreach ($value['access_file_principal'] as $second_key => $data) {
                    if (array_key_exists("principal_file_access_name", $data)) {
                        unset($detailArray[$key]['access_file_principal'][$second_key]['principal_file_access_name']);
                    }
                }
            }
            elseif (isset($value['access_file_principal_group'])) {
                foreach ($value['access_file_principal_group'] as $second_key => $data) {
                    if (array_key_exists("principal_group_access_file_name", $data)) {
                        unset($detailArray[$key]['access_file_principal_group'][$second_key]['principal_group_access_file_name']);
                    }
                }
            }

            elseif (isset($value['access_webbooking_principal_group'])) {
                foreach ($value['access_webbooking_principal_group'] as $second_key => $data) {
                    if (array_key_exists("principal_group_web_access_name", $data)) {
                        unset($detailArray[$key]['access_webbooking_principal_group'][$second_key]['principal_group_web_access_name']);
                    }
                }
            }
            elseif (isset($value['access_doc_dist'])) {
                foreach ($value['access_doc_dist'] as $second_key => $data) {
                    if (array_key_exists("doc_access_type", $data)) {
                        unset($detailArray[$key]['access_doc_dist'][$second_key]['doc_access_type']);
                    }
                }
            }
        }

        $request->merge(['detail' => $detailArray]);

        $this->validate($request,[
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'name' => 'required',
            'detail' => 'required'
        ]);

        if($request['is_show_all_tickets'] == "1")
        {
            $request['is_show_all_tickets'] = 1;
        }
        if($request['is_post_cs'] == "1")
        {
            $request['is_post_cs'] = 1;
        }

        $validationResponse = $this->handleValidation($request, 'add');

        if ($validationResponse) {
            return $validationResponse;
        }

        $set_request = SetRequestGlobal('addUser', collectDeviceInfo(), $request, array('created_at'=>'created_at'), 'user_code');

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
        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetUser, deviceInfo:collectDeviceInfo(), filter:array('id' => $id));
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $setFeatures = $this->computeSetFeatures();

        $generate_nav_button = generateNavbutton($decodedData,'back'.$setFeatures,'show', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'view');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['user'] = $decodedData;
        $formData['is_show_all_tickets'] = $this->is_show_all_tickets;
        $formData['is_post_cs'] = $this->is_post_cs;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $set_request = SetRequestGlobal(action:$this->globalVariable->actionGetUser, deviceInfo:collectDeviceInfo(), filter:array('id' => $id));
        $result = $this->getApi($set_request);
        $decodedData = removeArrayBracket($result['data']['data']);

        $generate_nav_button = generateNavbutton($decodedData,'back|save','edit', '', $this->globalVariable->menuRoute, $this->globalVariable->menuParam);

        $formData = $this->objResponse($this->globalVariable->module, $this->globalVariable->subModule, $this->globalVariable->menuUrl, 'edit');

        $formData['list_nav_button'] = $generate_nav_button;
        $formData['user'] = $decodedData;
        $formData['is_show_all_tickets'] = $this->is_show_all_tickets;
        $formData['is_post_cs'] = $this->is_post_cs;
        $formData['selectActive'] = $this->arrayIsActive;

        return view($this->form_file, $formData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request,[
            'username' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'required|confirmed|min:8',
            'name' => 'required',
        ]);
        $make_detail = $request->input('detail');
        if($request['is_show_all_tickets'] == "1")
        {
            $request['is_show_all_tickets'] = 1;
        }
        if($request['is_post_cs'] == "1")
        {
            $request['is_post_cs'] = 1;
        }
        $detailArray = json_decode($make_detail, true);

        foreach ($detailArray as $key => $value) {
            if (isset($value['user_branch'])) {
                foreach ($value['user_branch'] as $second_key => $data) {
                    if (array_key_exists("branch_name", $data)) {
                        unset($detailArray[$key]['user_branch'][$second_key]['branch_name']);
                    }
                }
            }
            elseif (isset($value['user_branch_report'])) {
                foreach ($value['user_branch_report'] as $second_key => $data) {
                    if (array_key_exists("branch_report_name", $data)) {
                        unset($detailArray[$key]['user_branch_report'][$second_key]['branch_report_name']);
                    }
                }
            }
            elseif (isset($value['access_application'])) {
                foreach ($value['access_application'] as $second_key => $data) {
                    if (array_key_exists("webstite_access_name", $data)) {
                        unset($detailArray[$key]['access_application'][$second_key]['webstite_access_name']);
                    }
                }
            }
            elseif (isset($value['user_company'])) {
                foreach ($value['user_company'] as $second_key => $data) {
                    if (array_key_exists("user_company_name", $data)) {
                        unset($detailArray[$key]['user_company'][$second_key]['user_company_name']);
                    }
                }
            }
            elseif (isset($value['user_group'])) {
                foreach ($value['user_group'] as $second_key => $data) {
                    if (array_key_exists("user_group_name", $data)) {
                        unset($detailArray[$key]['user_group'][$second_key]['user_group_name']);
                    }
                }
            }
            elseif (isset($value['access_file_principal'])) {
                foreach ($value['access_file_principal'] as $second_key => $data) {
                    if (array_key_exists("principal_file_access_name", $data)) {
                        unset($detailArray[$key]['access_file_principal'][$second_key]['principal_file_access_name']);
                    }
                }
            }
            elseif (isset($value['access_file_principal_group'])) {
                foreach ($value['access_file_principal_group'] as $second_key => $data) {
                    if (array_key_exists("principal_group_access_file_name", $data)) {
                        unset($detailArray[$key]['access_file_principal_group'][$second_key]['principal_group_access_file_name']);
                    }
                }
            }

            elseif (isset($value['access_webbooking_principal_group'])) {
                foreach ($value['access_webbooking_principal_group'] as $second_key => $data) {
                    if (array_key_exists("principal_group_web_access_name", $data)) {
                        unset($detailArray[$key]['access_webbooking_principal_group'][$second_key]['principal_group_web_access_name']);
                    }
                }
            }
            elseif (isset($value['access_doc_dist'])) {
                foreach ($value['access_doc_dist'] as $second_key => $data) {
                    if (array_key_exists("doc_access_type", $data)) {
                        unset($detailArray[$key]['access_doc_dist'][$second_key]['doc_access_type']);
                    }
                }
            }
        }

        $request->merge(['detail' => $detailArray]);

        $validationResponse = $this->handleValidation($request, 'update');

        if ($validationResponse) {
            return $validationResponse;
        }

        $set_request = SetRequestGlobal('updateUser', collectDeviceInfo(), $request);

        $result = $this->sendApi($set_request, 'put', $id);

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
        $set_request = SetRequestGlobal('softDeleteUser', collectDeviceInfo());

        $result = $this->sendApi($set_request, 'delete', $id);
        // dd($result);
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
        $id = Session::get('id');
        $authToken = Session::get('auth_token');

        $response = Http::accept('application/json')->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->get($this->getUrlBase('profile/') . $id);

        $result = json_decode($response);

        $formData = $this->objResponse('Dashboard', 'Profil', 'profil-user', 'index');
        $formData['list_users'] = $result['data']['data'];

        return view('profile-user',$formData);
    }

    public function updateProfileSend(Request $request)
    {
        $authToken = Session::get('auth_token');
        $user = Auth::user();

        $response = Http::accept('application/json')->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->post($this->getUrlBase('profile'),
            $request,
        );

        $result = json_decode($response,true);

        if($result['success'] == false)
        {
            return back()->withErrors($result['error'])->withInput();
        }

        return redirect('/')->with('message', $result['data']['data']['message']);
    }
}
