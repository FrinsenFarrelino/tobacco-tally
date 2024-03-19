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

    public function modelName($string)
    {
        $set = "App\\Models\\" . $string;
        return $set;
    }

    public function getData($action) {
        $actionsToModel = $this->globalActionController->getActionsToModel();
        $query = $this->modelName($actionsToModel[$action])::query();

        $modelClass = $this->modelName($actionsToModel[$action]);
        $modelInstance = new $modelClass;
        $tableName = $modelInstance->getTable();

        if($action == 'getCity') {
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'cities.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'cities.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'cities.deleted_by');
            $query->select(
                    'cities.*', 
                    'provinces.name as province_name',
                    'created_by_user.name as created_by',
                    'updated_by_user.name as updated_by',
                    'deleted_by_user.name as deleted_by',
                );
        } elseif($action == 'getSubdistrict') {
            $query->leftJoin('cities', 'cities.id', '=', 'subdistricts.city_id' );
            $query->leftJoin('provinces', 'provinces.id', '=', 'cities.province_id');
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'subdistricts.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'subdistricts.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'subdistricts.deleted_by');
            $query->select(
                'subdistricts.*', 
                'cities.name as city_name',
                'provinces.name as province_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } elseif($action == 'getWarehouse') {
            $query->leftJoin('branches', 'branches.id', '=', 'warehouses.branch_id' );
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', 'warehouses.created_by');
            $query->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', 'warehouses.updated_by');
            $query->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', 'warehouses.deleted_by');
            $query->select(
                'warehouses.*', 
                'branches.name as branch_name',
                'created_by_user.name as created_by',
                'updated_by_user.name as updated_by',
                'deleted_by_user.name as deleted_by',
            );
        } 
        else {
            $query->leftJoin('users as created_by_user', 'created_by_user.id', '=', $tableName . '.created_by')
                ->leftJoin('users as updated_by_user', 'updated_by_user.id', '=', $tableName . '.updated_by')
                ->leftJoin('users as deleted_by_user', 'deleted_by_user.id', '=', $tableName . '.deleted_by')
                ->select(
                    $tableName . '.*',
                    'created_by_user.name as created_by',
                    'updated_by_user.name as updated_by',
                    'deleted_by_user.name as deleted_by',
                );
        }

        $data = $query->get();

        return $data;
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
            $data = $this->getData($action);
            
            // Modify each row's data to include buttons
            foreach ($data as $row) {
                $row->editUrl = route($request->route.'.edit', [$request->route => $row->id]);
                $row->destroyUrl = route($request->route.'.destroy', [$request->route => $row->id]);
                $row->showUrl = route($request->route.'.show', [$request->route => $row->id]);

                // Remove the 'id' column from the row
                if($row->is_active || $row->is_active === 1) {
                    $row->is_active = 'Active';
                } elseif(!$row->is_active || $row->is_active === 0) {
                    $row->is_active = 'Inactive';
                }

                unset($row->id);
            }
            
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'error' => 'Action not found'], 404);
        }
    }
}
