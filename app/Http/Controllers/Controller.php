<?php

namespace App\Http\Controllers;

use App\Handlers\StatusMessageCode;
use GlobalActionController;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getUrlBase($path = '', $env_name = 'API_URL')
    {
        $url_base = env($env_name);

        $url_api = $url_base . $path;

        return $url_api;
    }

    public function sendApi($set_request, $method, $id = null, $url = 'setData')
    {
        $authToken = Session::get('auth_token');

        $new_url = $this->getUrlBase($url);

        if ($method == 'delete') {
            $new_url = $this->getUrlBase('deleteData/' . $id);
        } elseif ($method == 'put') {
            $new_url = $this->getUrlBase('updateData/' . $id);
        }

        $response = Http::accept('application/json')->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->$method($new_url, $set_request);

        $result = json_decode($response, true);

        return $result;
    }

    public function getApi($set_request, $url = 'getData', $method = 'POST', $id = null)
    {

        $auth_token = Session::get('auth_token');

        $new_url = $this->getUrlBase($url);

        if ($id != null) {
            $new_url = $this->getUrlBase($url . '/' . $id);
        }

        $client = new \GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $auth_token, // Replace 'YourAccessToken' with your actual access token
            'Content-Type' => 'application/json', // Modify this according to the content type you're sending
            'Accept' => 'application/json',
        ];

        $response = $client->request($method, $new_url, [
            'timeout' => 60, // Set timeout to 60 seconds
            'headers' => $headers,
            'json' => $set_request
        ]);

        $responseBody = $response->getBody()->getContents();
        // $response = Http::accept('application/json')->withHeaders([
        //     'Authorization' => 'Bearer ' . $auth_token,
        // ])->$method($new_url, $set_request);

        return json_decode($responseBody, true);
    }

    public function objResponse($title, $subtitle, $menu, $mode)
    {
        // Access the "home.name" key in the language file
        $title = Lang::get('subMenu')[$title];

        // Access the "home.dashboard.name" key in the language file
        $submodule = Lang::get($subtitle)['submodule'] ?? '';
        $subtitle = Lang::get($subtitle)['name'];

        $objResponse = [
            'title' => $title,
            'subtitle' => $subtitle,
            'submodule' => $submodule,
            'menu' => $menu,
            'mode' => $mode
        ];

        return $objResponse;
    }

    public function setPrivButton($code)
    {
        $setValueFeature = '';
        if (Session::get('user_group')['name'] === 'Admin') {
            if ($code == 'locked_principal') {
                $setValueFeature .= '|' . 'locked';
            } elseif ($code == 'pre_order') {
                $setValueFeature .= '|' . 'add';
                $setValueFeature .= '|' . 'edit';
                $setValueFeature .= '|' . 'delete';
                $setValueFeature .= '|' . 'rollbackdraft';
                $setValueFeature .= '|' . 'rollbackreqrfq';
            } else {
                $setValueFeature .= '|' . 'add';
                $setValueFeature .= '|' . 'edit';
                $setValueFeature .= '|' . 'delete';
            }
        } else {
            if (Session::has('list_menu') && Session::has('access_menu')) {
                $listMenu = Session::get('list_menu');
                $accessMaster = Session::get('access_menu');

                foreach ($listMenu as $list_menu) {
                    if ($list_menu['code'] == $code) {
                        foreach ($accessMaster as $access_master) {
                            if ($access_master['menu_id'] == $list_menu['id']) {
                                if ($access_master['add'] == true) {
                                    if ($code == 'locked_principal') {
                                        $setValueFeature .= '|' . 'locked';
                                    } else {
                                        $setValueFeature .= '|' . 'add';
                                    }
                                }
                                if ($access_master['edit'] == true) {
                                    if ($code == 'locked_principal') {
                                        $setValueFeature .= '|' . 'locked';
                                    } elseif ($code == 'pre_order') {
                                        $setValueFeature .= '|' . 'edit';
                                        $setValueFeature .= '|' . 'rollbackdraft';
                                        $setValueFeature .= '|' . 'rollbackreqrfq';
                                    } else {
                                        $setValueFeature .= '|' . 'edit';
                                    }
                                }
                                if ($access_master['delete'] == true) {
                                    if ($code == 'locked_principal') {
                                        $setValueFeature .= '|' . 'locked';
                                    } else {
                                        $setValueFeature .= '|' . 'delete';
                                    }
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $setValueFeature;
    }

    public function sendResponse($status, $code, $result = '', $error = '')
    {
        $obj = new StatusMessageCode();
        $message = $obj->setCustomMessageStatusCode($code);
        $error = $obj->setCustomMessageErrorCode($code);

        $response = [
            'success' => $status,
            'status_code' => $code,
            'message'    => $message,
            'errors' => $error
        ];

        if ($code == 0 || $code == 200) {
            $response = [
                'success' => $status,
                'status_code' => $code,
                'data'    => $result,
            ];
            return $response;
        } elseif ($code == Response::HTTP_INTERNAL_SERVER_ERROR || $code == Response::HTTP_NOT_FOUND || $code == Response::HTTP_BAD_REQUEST) {
            $response = [
                'success' => $status,
                'status_code' => $code,
                'message'    => $result,
                'errors' => ''
            ];
            return $response;
        }
        return $response;
    }

    public function errorHandle($e, $request)
    {
        $request = request();
        if ($e instanceof MethodNotAllowedHttpException) {
            $response = [
                'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => $e->getMessage(),
            ];
            return $response;
        }

        if ($e instanceof AuthenticationException) {
            $response = [
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage(),
            ];
            return $response;
        }

        if ($e instanceof NotFoundHttpException) {
            $response = [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
            return $response;
        }

        if ($e instanceof ModelNotFoundException) {
            $response = [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Model not found for : ' . $e->getMessage(),
            ];
            return $response;
        }

        $log =  date('d-m-Y h:i:s')
            . PHP_EOL . $request->method() . ' ' . $request->getRequestUri()
            . PHP_EOL . 'Error : ' . $e->getCode() . ', ' . $e->getMessage() . ' at ' . strstr($e->getFile(), 'sabp-api') . ' [Line ' . $e->getLine() . ']'
            . PHP_EOL . 'Request : ' . json_encode($request->all())
            . PHP_EOL . PHP_EOL;

        file_put_contents("../storage/logs/" . date('Y-m-d') . "-error-logs.txt", $log, FILE_APPEND);

        if ($e instanceof QueryException) {
            $response = [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
            return $response;
        }

        $response = [
            'code' => $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stackTrace' => $e->getTraceAsString(),
        ];
        return $response;
    }
}
