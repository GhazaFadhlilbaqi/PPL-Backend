<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
      $this->reportable(function (Throwable $e) {
          Integration::captureUnhandledException($e);
      });
    }

    public function render($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            $errorMessages = collect($exception->errors())->flatten()->implode(', ');
            return response()->json([
                'success' => false,
                'message' => $errorMessages
            ], 422);
        }

        if ($exception instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        $json_response = [
            'success' => false,
            'message' => "Terdapat masalah pada server, mohon hubungi customer service"
        ];
        if (config('app.debug')) {
            $json_response['trace'] = $exception->getTrace();
        }
        return response()->json($json_response, 500);
    }
}
