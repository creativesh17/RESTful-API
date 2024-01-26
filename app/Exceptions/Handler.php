<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
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
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception) {
        if($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if($exception instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("Does not exist any {$modelName} with the specified identificator!", Response::HTTP_NOT_FOUND);
        }

        if($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if($exception instanceof AuthorizationException) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        if($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse("The specified method for the request is invalid!", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if($exception instanceof NotFoundHttpException) {
            return $this->errorResponse("The specified URL can not be found!", Response::HTTP_NOT_FOUND);
        }

        if($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if($exception instanceof QueryException) {
            $errorCode = $exception->errorInfo[1];

            if($errorCode == 1451) {
                return $this->errorResponse("Can not remove this resource permanently. It is related with any other resource", Response::HTTP_CONFLICT);
            }

            if($errorCode == 1048) {
                return $this->errorResponse("Check if any value is null or missing!", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }

        if(config('app.debug')) {
            return parent::render($request, $exception);
            // change APP_DEBUG in .env file to false during production
        }

        return $this->errorResponse("Unexpected Exception. Try Later!", Response::HTTP_INTERNAL_SERVER_ERROR);

    }

    protected function unauthenticated($request, AuthenticationException $exception) {
        if($this->isFrontend($request)) {
            return redirect()->guest('login');
        }

        return $this->errorResponse("Unauthenticated!", Response::HTTP_UNAUTHORIZED);
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request) {
        $errors = $e->validator->errors()->getMessages();

        if($this->isFrontend($request)) {
            return $request->ajax() ? response()->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY) : redirect()->back()->withInput($request->input())->withErrors($errors);
        }

        return $this->errorResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function isFrontend($request) {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }

}
