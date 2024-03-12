<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(Request $request, User $user)
    {
        return $this->sendResponse(true, Response::HTTP_OK, $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = User::findOrFail($request->id);

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->sendResponse(false, Response::HTTP_BAD_REQUEST, ['message' => 'Password is incorrect']);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        return $this->sendResponse(true, Response::HTTP_OK, ['message' => 'Your password has been change successfully']);
    }
}
