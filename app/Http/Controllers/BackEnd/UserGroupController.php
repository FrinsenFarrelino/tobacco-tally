<?php

namespace App\Http\Controllers\BackEnd;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SetUserGroupPriviledgeRequest;
use App\Http\Services\UserGroupService;
use App\Models\UserGroup;


class UserGroupController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function setPriviledge(UserGroupService $userGroupService, SetUserGroupPriviledgeRequest $request, UserGroup $userGroup)
    {
        $user = auth()->user();
        $response = $userGroupService->setPriviledge($request->validated(), $userGroup, $user);
        return $this->sendResponse(true, Response::HTTP_OK, $response);
    }
}
