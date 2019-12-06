<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Overtrue\Socialite\AuthorizeFailedException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use EasyWeChat\Kernel\Exceptions\HttpException as EasyWeChatHttpException;
use EasyWeChat\Kernel\Exceptions\DecryptException as EasyWeChatDecryptException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ApiCustomException::class,
        AuthorizeFailedException::class,
        ThrottleRequestsException::class,
        ValidationException::class,
        EasyWeChatHttpException::class,
        EasyWeChatDecryptException::class
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        try {
            $rid = app('sauth')->getRidWhenUnauth();
        } catch (Exception $e) {
            $rid = 'undefined';
        }

        $data = ['rid' => $rid];

        if ($exception instanceof ApiCustomException) {
            return response()->json([
                'msg'  => $exception->getMessage(),
                'code' => $exception->getCode()
            ], $exception->getCode());
        }

        if ($exception instanceof AuthorizeFailedException) {
            \Log::error("AuthorizeFailedException: ".$exception->getMessage(), $data);
            return response()->json([
                'msg'  => '微信授权异常，稍后再试',
                'code' => 500
            ], 500);
        }

        if ($exception instanceof EasyWeChatDecryptException) {
            \Log::error("EasyWeChatDecryptException: ".$exception->getMessage(), $data);
            return response()->json([
                'msg'  => '微信授权异常，稍后再试',
                'code' => 500
            ], 500);
        }

        if ($exception instanceof ThrottleRequestsException) {
            \Log::error("ThrottleRequestsException: ".$exception->getMessage(), $data);
            return response()->json([
                'msg'  => '操作过于频繁，稍后再试',
                'code' => 429
            ], 429);
        }

        if ($exception instanceof ValidationException) {
            \Log::error("ValidationException: ".$exception->getMessage(), $exception->errors());
            return response()->json([
                'msg'  => '缺少必填参数,非法请求',
                'code' => 422
            ], 422);
        }

        if ($exception instanceof EasyWeChatHttpException) {
            \Log::error("EasyWeChatHttpException: ".$exception->getMessage(), $data);
            return response()->json([
                'msg'  => '微信接口异常',
                'code' => 500
            ], 500);
        }

        if ($exception instanceof Exception) {
            $msg = $exception->getMessage();

            if ($msg == 'Trying to get property of non-object')
            {
                $msg = $exception->getTraceAsString();
            }

            \Log::error("Exception: ".$msg, $data);
            return response()->json([
                'msg'  => '未知错误类型,请联系店主',
                'code' => 500
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
