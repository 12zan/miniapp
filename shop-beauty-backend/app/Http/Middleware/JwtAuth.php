<?php

namespace App\Http\Middleware;

use App\Biz\Jwt;
use Closure;

class JwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //如果是本地环境，并且头部携带了openid,不走中间件，主要用来本地调试接口
        if (env('APP_ENV') == 'impossible' && $request->hasHeader('openid')) {
            return $next($request);
        } else {
            (new Jwt)->auth($request);
        }

        return $next($request);
    }
}
