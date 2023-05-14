<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param $request
     * @param Throwable $e
     *
     * @return \Illuminate\Http\Response|JsonResponse|Response
     * @throws Throwable
     */
    public function render($request, Throwable $e): \Illuminate\Http\Response|JsonResponse|Response
    {
        if ($e instanceof HttpException) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        return parent::render($request, $e);
    }
}
