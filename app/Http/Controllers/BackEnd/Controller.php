<?php

namespace App\Http\Controllers\BackEnd;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Handlers\StatusMessageCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
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

        if($code == 0 || $code == 200)
        {
            $response = [
                'success' => $status,
                'status_code' => $code,
                'data'    => $result,
            ];
            return $this->json($response);
        }
        elseif($code == Response::HTTP_INTERNAL_SERVER_ERROR || $code == Response::HTTP_NOT_FOUND || $code == Response::HTTP_BAD_REQUEST)
        {
            $response = [
                'success' => $status,
                'status_code' => $code,
                'message'    => $result,
                'errors' => ''
            ];
            return $this->json($response);
        }
        return $this->json($response);
    }

    public function errorHandle(Throwable $e, $request)
    {
            $request = request();
            if ($e instanceof MethodNotAllowedHttpException) {
                return response([
                    'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                    'message' => $e->getMessage(),
                ], Response::HTTP_METHOD_NOT_ALLOWED);
            }

            if ($e instanceof AuthenticationException) {
                return response([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => $e->getMessage(),
                ], Response::HTTP_UNAUTHORIZED);
            }

            if ($e instanceof NotFoundHttpException) {
                return response([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'URL not found for : ' . $request->getPathInfo(),
                ], Response::HTTP_NOT_FOUND);
            }

            if ($e instanceof ModelNotFoundException) {
                return response([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Model not found for : ' . $e->getMessage(),
                ], Response::HTTP_NOT_FOUND);
            }

            $log =  date('d-m-Y h:i:s')
            . PHP_EOL . $request->method() . ' ' . $request->getRequestUri()
                . PHP_EOL . 'Error : ' . $e->getCode() . ', ' . $e->getMessage() . ' at ' . strstr($e->getFile(), 'sabp-api') . ' [Line ' . $e->getLine() . ']'
                . PHP_EOL . 'Request : ' . json_encode($request->all())
                . PHP_EOL . PHP_EOL;

            file_put_contents("../storage/logs/".date('Y-m-d')."-error-logs.txt", $log, FILE_APPEND);

            if ($e instanceof QueryException) {
                return response([
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response([
                'code' => $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stackTrace' => $e->getTraceAsString(),
            ], $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);

    }
}
