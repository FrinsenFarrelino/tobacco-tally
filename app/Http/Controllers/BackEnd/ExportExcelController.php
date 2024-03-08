<?php

namespace App\Http\Controllers\BackEnd;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GlobalExport;
use App\Http\Controllers\GlobalActionController;
use App\Http\Controllers\GlobalController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportExcelController extends Controller
{

    protected $globalActionController;
    protected $globalController;

    public function __construct(GlobalActionController $globalActionController, GlobalController $globalController)
    {
        $this->globalActionController = $globalActionController;
        $this->globalController = $globalController;
    }

    public function downloadExcel(Request $request)
    {
        $action = $request->get('action');

        $actionsToModel = $this->globalActionController->getActionsToModel();

        if (!array_key_exists($action, $actionsToModel)) {
            return $this->sendResponse(false, Response::HTTP_NOT_FOUND, 'Action Not Found!');
        }

        $name =  date('dmYhis_') . $request->get('name') . '.' . $request->get('excel_type');

        if($request->get('excel_type') == 'csv')
        {
            return Excel::download(new GlobalExport($request->get('mapping_table'), $actionsToModel[$action], $this->globalController), $name, \Maatwebsite\Excel\Excel::CSV);
        }
        else{
            return Excel::download(new GlobalExport($request->get('mapping_table'), $actionsToModel[$action], $this->globalController), $name, \Maatwebsite\Excel\Excel::XLSX);
        }

    }
}
