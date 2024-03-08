<?php

namespace App\Http\Controllers\BackEnd;

use Illuminate\Http\Request;
use App\Models\Branch;
use Symfony\Component\HttpFoundation\Response;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Branch::all('code','name');

        return $this->sendResponse(true, Response::HTTP_OK, $data);
    }

    public function branchWithUser(){
        $data = Branch::with('users')->get();
        return $this->sendResponse(true, Response::HTTP_OK, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // get request body
        $requestData = $request->toArray();
        $requestBody = $request->requestData;

        $data = Branch::create($requestBody);

        return $this->sendResponse(true, Response::HTTP_OK, $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        // get action in request
        $action = $request->action;

        //check if action exists
        foreach (ActionEnum::cases() as $case) {
            if($case->name == $action)
            {
                $data = $this->global_interface->show($case->value, $id);

                return $this->sendResponse(true, Response::HTTP_OK, $data);
            }
        }
        return $this->sendResponse(false, 101);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // get action in request
        $action = $request->action;

        // get request body
        $requestData = $request->toArray();
        $requestBody = $request->requestData;

        //check if action exists
        foreach (ActionEnum::cases() as $case) {
            if($case->name == $action)
            {
                $data = $this->global_interface->update($case->value,$id,$requestBody);

                return $this->sendResponse(true, Response::HTTP_OK, $data);
            }
        }
        return $this->sendResponse(false, 101);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // get action in request
        $action = $request->action;

        //check if action exists
        foreach (ActionEnum::cases() as $case) {
            if($case->name == $action)
            {
                $data = $this->global_interface->delete($case->value, $id);

                return $this->sendResponse(true, Response::HTTP_OK, $data);
            }
        }
        return $this->sendResponse(false, 101);
    }
}
