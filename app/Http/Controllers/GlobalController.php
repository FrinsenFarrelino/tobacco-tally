<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GlobalVariable;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GlobalController extends Controller
{
    private $globalVariable;
    protected $globalActionController;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalVariable = $globalVariable;
        $this->globalVariable->ModuleGlobal('Warning', 'Warning', 'warning', 'warning', 'warning');
    }

    public function switchLang($lang, Request $request)
    {
        $getUrlPrev = URL::previous();

        if (array_key_exists($lang, Config::get('languages'))) {
            Session::put('applocale', $lang);
        }

        return redirect($getUrlPrev);
    }

    public function showWarning()
    {
        $message = session('message');

        $objResponse = [
            'title' => $this->globalVariable->module,
            'subtitle' =>  $this->globalVariable->subModule,
            'menu' => $this->globalVariable->menuUrl,
            'mode' => 'index'
        ];

        return view('warning', $objResponse)->with('message', $message); // Load the 'warning.blade.php' view
    }

    private function computeSetFeatures($code)
    {
        $setValueFeature = $this->setPrivButton($code);

        return $setValueFeature;
    }

    public function getStatus(Request $request)
    {
        // Get parameters from the request
        $status = 0;
        if ($request->get('status') == "true") {
            $status = 1;
        }
        $mode = $request->get('mode');

        // Your formatStatus logic
        $formattedStatus = formatStatus($status, $mode);
        return json_encode(['formattedStatus' => $formattedStatus]);
    }

    public function getAjaxDataTable(Request $request, $action)
    {
        $select = ['id'];
        foreach ($request->columns as $data) {
            if ($data['name'] !== 'action' && $data['name'] !== null)
                $select[] = $data['name'];
        }
        $actionsToModel = $this->globalActionController->getActionsToModel();
        if (isset($actionsToModel[$action])) {
            $modelName = 'App\Models\\' . $actionsToModel[$action];
            $data = $modelName::select($select)->get();

            // Modify each row's data to include buttons
            foreach ($data as $row) {
                $editUrl = route($request->route.'.edit', [$request->route => $row->id]);
                $destroyUrl = route($request->route.'.destroy', [$request->route => $row->id]);
                $showUrl = route($request->route.'.show', [$request->route => $row->id]);

                $row->action = '<a href="' . $showUrl . '" class="btn btn-secondary btn-sm rounded-circle"><i class="fas fa-eye"></i></a>'
                    . '<a href="' . $editUrl . '" class="btn btn-warning btn-sm ml-2 rounded-circle"><i class="fas fa-pencil-alt"></i></a>'
                    . '<a href="' . $destroyUrl . '" class="btn btn-danger btn-sm ml-2 rounded-circle"><i class="fas fa-trash-alt"></i></a>';

                // Remove the 'id' column from the row
                if($row->is_active || $row->is_active === 1) {
                    $row->is_active = 'Active';
                } else {
                    $row->is_active = 'Inactive';
                }
                unset($row->id);
            }
            
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'error' => 'Action not found'], 404);
        }
    }

    public function autoComplete(Request $request)
    {
        $query = '';
        $input_param = '';
        $filter = [];
        $search_key = [];
        $custom_filter = [];

        if ($request->get('search') != null) {
            $query = $request->get('search');
        }
        if ($request->get('input_param') != null) {
            $input_param = $request->get('input_param');
        }

        $arrayFilter = array('skip' => 0, 'take' => 50);
        $arrayFilter['is_active'] = "1";
        if (!empty($input_param)) {
            $set_input_param = json_decode($input_param, true);

            // custom filter
            if (!empty($set_input_param)) {
                foreach ($set_input_param as $key => $value) {
                    $set_value = $value['query'];

                    if ($value['query'] == 'on') {
                        $set_value = 'true';
                    }

                    if ($value['key'] == 'rfqs.is_customs_clearance' || $value['key'] == 'rfqs.is_freight') {
                        $search_key[] = array(
                            'key' => $value['key'],
                            'term' => $value['term'],
                            'query' => $set_value,
                        );
                    } else {
                        $custom_filter[] = array(
                            'key' => $value['key'],
                            'term' => $value['term'],
                            'query' => $set_value,
                        );
                    }
                }
            }
        }

        foreach ($request->get('search_term') as $value) {
            $param = explode("|", $value);
            $search_key[] = array(
                'key' => $param[0],
                'term' => $param[1],
                'query' => $query
            );
        }

        $set_request = SetRequestGlobal(
            action: $request->get('action'),
            deviceInfo: collectDeviceInfo(),
            filter: $arrayFilter,
            search: $search_key
        );

        if (!empty($request->get('filter')) || $request->get('filter') != null) {
            $filter = json_decode($request->get('filter'), true);
            // custom filter
            if (!empty($filter)) {
                foreach ($filter as $key => $value) {
                    $custom_filter[] = array(
                        'key' => $key,
                        'term' => 'equal',
                        'query' => $value
                    );
                }
            }
        }

        if (!empty($custom_filter)) {
            $set_request = SetRequestGlobal(
                action: $request->get('action'),
                deviceInfo: collectDeviceInfo(),
                filter: $arrayFilter,
                search: $search_key,
                custom_filters: $custom_filter
            );
        }

        $result = $this->getApi($set_request);
        if ($result['success'] == false) {
            return json_encode($result);
        }

        $temp_response = [];
        $response = [];

        if ($request->get('is_grid') != null) {
            foreach ($result['data']['data'] as $data) {
                $temp_label = '';
                $show_value = $request->get('show_value');

                for ($i = 0; $i < count($show_value); $i++) {
                    if ($i > 0) {
                        $temp_label .= " - " . $data[$show_value[$i]];
                    } else {
                        $temp_label .= $data[$show_value[$i]];
                    }
                }

                $temp_result = '';
                $result_show = $request->get('result_show');

                for ($i = 0; $i < count($result_show); $i++) {
                    if ($i > 0) {
                        $temp_result .= " - " . $data[$result_show[$i]];
                    } else {
                        $temp_result .= $data[$result_show[$i]];
                    }
                }
                $data['visible'] = $temp_label;
                $data['result'] = $temp_result;
                array_push($temp_response, $data);
            }
            $response = [
                'items' => $temp_response
            ];
        } else {
            foreach ($result['data']['data'] as $data) {
                $temp_label = '';
                $decode = json_decode($request->get('show_value'));

                for ($i = 0; $i < count($decode); $i++) {
                    if ($i > 0) {
                        $temp_label .= " - " . $data[$decode[$i]];
                    } else {
                        $temp_label .= $data[$decode[$i]];
                    }
                }
                $data['label'] = $temp_label;

                $response[] = array("value" => $data['id'], "label" => $data['label'], "data" => $data);
            }
        }

        return json_encode($response);
    }

    public function getBrowseData(Request $request)
    {
        $set_table = renderTableGlobalAjax($request->get('head_table'));

        $header_content = renderModelHeaderForm($request->get('table_name'));
        $filter = [];
        $input_param = [];
        if ($request->get('filter')) {
            $filter = json_decode($request->get('filter'), true);
            // $filter = $request->get('filter');
        }
        if ($request->get('input_param')) {
            $input_param = json_decode($request->get('input_param'), true);
            // $input_param = $request->get('filter');
        }

        $initTableModal = initializeDataTableModal($request->get('action'), $request->get('field_table'), $request->get('output_param'), $filter, $input_param);
        $response = [
            'header' => $header_content,
            'body_content' => $set_table,
            'footer' => '',
            'init_table_modal' => $initTableModal,
        ];

        return json_encode($response);
    }

    public function renderWarn(Request $request)
    {
        $messageWarn = $request->input('messageWarn');
        $errors = []; // You might want to pass actual errors here

        $html = renderAlertWarning('500', $messageWarn, $errors);

        return $html;
    }

    //upload file
    public function upload(Request $request)
    {
        $response = [];
        if ($request->hasFile('main_file')) {

            $image = $request->file('main_file');

            $fileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

            $current_timestamp = Carbon::now()->timestamp;
            $newFileName = preg_replace('/[^A-Za-z0-9]/', '', $fileName);

            $file_name = $current_timestamp . $newFileName . '.' . $image->getClientOriginalExtension();
            $path = $request->get('path');
            $folder = 'uploads';

            if ($request->get('folder') != '') {
                $folder = $request->get('folder');
            }

            $directory_path = $path . '/' . $file_name;

            $link_path = env('URL_SFTP') . '/' . env('FOLDER_SFTP') . '/';

            $dir_folder = $folder . '/' . $path;

            $request->merge(['directory_path' => $directory_path, 'file_name' => $file_name, 'link_path' => $link_path]);

            $validationResponse = $this->handleValidation($request, 'add');

            if ($validationResponse) {
                return $validationResponse;
            }

            $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'];

            $contentType = mime_content_type($image->getPathname());

            $code_file = 'code_image';
            if (!in_array($contentType, $allowedMimeTypes)) {
                $code_file = 'code_file';
            }
            $set_request = SetRequestGlobal('addFile', collectDeviceInfo(), $request, [], $code_file);

            try {
                $stored_file_path = Storage::disk('public')->putFileAs($dir_folder, $image, $file_name, 'public');
                $response = [
                    "success" => true,
                    "status_code" => 200,
                    'message' => 'Success upload file',
                ];
            } catch (\Exception $e) {
                $response = [
                    "success" => false,
                    "status_code" => 404,
                    'message' => 'Failed upload file',
                    'errors' => $e->getMessage(),
                ];
            }

            if ($response['success']) {
                $result = $this->sendApi($set_request, 'post');

                if (!$result['success']) {
                    $response = [
                        "success" => false,
                        "status_code" => 404,
                        'message'    => 'Cannot add to db',
                        'errors' => $result,
                    ];
                }
            }

            // You can also store the file path in the database or do other actions
            return json_encode($response);
        }
    }

    //delete files
    public function deleteFileDb(string $id)
    {
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetPrincipalDataFile, deviceInfo: collectDeviceInfo(), filter: array('id' => $id));
        $result = $this->getApi($set_request);

        $decodedData = removeArrayBracket($result['data']['data']);

        $filePath = $decodedData['directory_path'];

        $fullFilePath = 'uploads/' . $filePath;

        if (Storage::disk('public')->exists($fullFilePath)) {
            Storage::disk('public')->delete($fullFilePath);
            $set_request = SetRequestGlobal('softDeleteFile', collectDeviceInfo());

            $result = $this->sendApi($set_request, 'delete', $id);

            if ($result['success'] == false) {
                return back()->withErrors($result['message'])->withInput();
            }
        } else {
            return back()->withErrors("File does not exist or could not be deleted.")->withInput();
        }

        return redirect()->back();
    }

    public function callRenderBody(Request $request)
    {
        try {
            $get_type = $request->get('type');
            $param = '';
            $text = '';
            if ($request->get('render') != null) {
                $get_render = json_decode($request->input('render'), true);
            }
            if ($request->get('class_icon') != null) {
                $class_icon = $request->get('class_icon');
            }
            $div = 'no';
            if ($request->get('div') != null) {
                $div = $request->get('div');
            }
            if ($request->get('color') != null) {
                $color = $request->get('color');
            }
            if ($request->get('title') != null) {
                $title = $request->get('title');
            }
            if ($request->get('text') != null) {
                $text = $request->get('text');
            }
            if ($request->get('id') != null) {
                $id = $request->get('id');
            }
            if ($request->get('route') != null) {
                $route = $request->get('route');
            }
            if ($request->get('param') != null) {
                $param = $request->get('param');
            }
            $category = '';
            if ($request->get('category') != null) {
                $category = $request->get('category');
            }
            if ($request->get('text_button_cancel') != null) {
                $text_button_cancel = $request->get('text_button_cancel');
            }
            if ($request->get('text_button_ok') != null) {
                $text_button_ok = $request->get('text_button_ok');
            }
            $method = "delete";
            if ($request->get('method') != null) {
                $method = $request->get('method');
            }
            $name = "id";
            if ($request->get('name') != null) {
                $name = $request->get('name');
            }
            $is_footer = '';
            if ($request->get('is_footer') != '') {
                $is_footer = $request->get('is_footer');
            }

            $header_content = "";
            $body_content = "";
            $footer = "";

            // Your helper function logic here
            if ($get_type == 'alert') {
                $body_content = renderModelBodyAlert($class_icon, $color, $title, $text);
                $footer = renderModelFooter($text_button_cancel, $text_button_ok, "btn btn-dark", "btn btn-primary");
            } elseif ($get_type == 'success') {
                $body_content = renderModelBodyConfirmation($class_icon, $color, $title, $text);
                $footer = renderModelFooter('', 'Oke', "", "btn btn-primary");
            } elseif ($get_type == 'confirmations') {
                $body_content = renderModelBodyConfirmation($class_icon, $color, $title, $text);
                $footer = renderModelFooterMethodConfirmation('button_cancel', 'button_confirm', "btn btn-dark", "btn btn-primary", $id, $route, $param, $method, $name);
            } else {
                $header_content = renderModelHeaderForm($title);
                $renderJSON = json_encode($get_render);
                $renderArray = json_decode($renderJSON, true);
                if ($category == '') {
                    $generateRoute = route($route);
                } else {
                    $generateRoute = generateRoute($route . '.' . $category, []);
                }

                $body_content = renderBodyModalForm($generateRoute, $method, '', $renderArray, $div, $text, '', '', '', '');

                if ($title == "Type Of Trade") {
                    $body_content = renderBodyModalForm($generateRoute, $method, '', $renderArray, $div, $text, 'Cancel', 'Confirm', 'btn btn-dark', 'btn btn-primary');
                }

                if ($is_footer == 'export_table') {
                    $footer = renderModelFooterExportTable('button_cancel', 'button_confirm', "btn btn-dark", "btn btn-primary", "downloadExcel()");
                }
            }

            $response = [
                'header_content' => $header_content,
                'body_content' => $body_content,
                'footer' => $footer,
            ];

            return json_encode($response);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()], 500);
        }
    }
}
