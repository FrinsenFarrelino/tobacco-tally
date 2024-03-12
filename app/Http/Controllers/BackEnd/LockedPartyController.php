<?php

namespace App\Http\Controllers\BackEnd;

use App\Models\Principal;
use App\Models\PrincipalBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LockedPartyController extends Controller
{
    public function index(Request $request)
    {

        $results = DB::table('principals AS a')
            ->leftJoin('principal_blacklists AS b', 'b.principal_id', '=', 'a.id')
            ->select('a.*', 'b.id AS principal_blacklist_id', DB::raw('CASE WHEN b.principal_id IS NOT NULL THEN TRUE ELSE FALSE END AS is_locked'))
            ->where('a.is_active', '=', 1)
            ->paginate($request->paginate ?? 10);

        return $this->sendResponse(
            true,
            Response::HTTP_OK,
            $results
        );
    }

    public function createLockedParty(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'principal_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return $this->sendResponse(true, Response::HTTP_BAD_REQUEST, $errors);
        }

        $principalId = $request->input('principal_id');

        $existingBlacklist = PrincipalBlacklist::where('principal_id', $principalId)->first();

        if ($existingBlacklist) {
            // unlocked      
            $existingBlacklist->forceDelete();
            $is_from_lnj2 = false;
            if ($request->has('is_lnj2')) {
                $is_from_lnj2 = true;
            }

            // send Data to LNJ2
            if ($is_from_lnj2 == false) {
                $baseSyncUrl = env('SYNC_BASE_URL');
                $objPayload = [
                    'data' => ['principal_id' => $principalId],
                    'endpoint_sync' => $baseSyncUrl . 'toLNJIS2/master/create/lockedPrincipal',
                ];

                try {
                    $response = Http::post($baseSyncUrl . 'toLNJIS2/master/create/lockedPrincipal', $objPayload);
                    if ($response) {
                        $timestamp = date('Y-m-d H:i:s');
                        $logEntry = "$timestamp - $response\n";
                        File::append(storage_path('logs/laravel.log'), $logEntry);
                    }
                } catch (\Exception $e) {
                    $timestamp = date('Y-m-d H:i:s');
                    $errorMessage = $e->getMessage();
                    $logEntry = "$timestamp - $errorMessage\n";
                    File::append(storage_path('logs/error.log'), $logEntry);
                    return false;
                }
            }


            return $this->sendResponse(true, Response::HTTP_OK, 'Principal unlocked successfully');
        } else {
            // locked
            $data = ['principal_id' => $principalId, 'remark' => $request->input('remark') ?? ''];
            $result = PrincipalBlacklist::create($data);

            $is_from_lnj2 = false;
            if ($request->has('is_lnj2')) {
                $is_from_lnj2 = true;
            }

            // send Data to LNJ2
            if ($is_from_lnj2 == false) {
                $baseSyncUrl = env('SYNC_BASE_URL');
                $objPayload = [
                    'data' => ['principal_id' => $principalId],
                    'endpoint_sync' => $baseSyncUrl . 'toLNJIS2/master/create/lockedPrincipal',
                ];

                try {
                    $response = Http::post($baseSyncUrl . 'toLNJIS2/master/create/lockedPrincipal', $objPayload);
                    if ($response) {
                        $timestamp = date('Y-m-d H:i:s');
                        $logEntry = "$timestamp - $response\n";
                        File::append(storage_path('logs/laravel.log'), $logEntry);
                    }
                } catch (\Exception $e) {
                    $timestamp = date('Y-m-d H:i:s');
                    $errorMessage = $e->getMessage();
                    $logEntry = "$timestamp - $errorMessage\n";
                    File::append(storage_path('logs/error.log'), $logEntry);
                    return false;
                }
            }

            return $this->sendResponse(true, Response::HTTP_OK, $result);
        }
    }
}
