<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use KodePandai\ApiResponse\ExceptionHandler as ApiExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            //
            if ($request->wantsJson() || str_contains($request->path(), 'api')) {
                return ApiExceptionHandler::renderAsApiResponse($e);
            }
        });
    }
}
