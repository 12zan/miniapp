<?php

namespace App\Http\Middleware;

use Closure;
use App\Repositories\AppRepository;

class CheckAppid
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
        if ($request->hasHeader('appid') && !empty($request->header('appid'))) {
            try {
                app(AppRepository::class)->findOrFailByAppid($request->header('appid'));
            } catch (\ApiCustomException $e) {
                throw new \ApiCustomException("非法appid", 410);
            }
        } else {
            throw new \ApiCustomException("缺少appid参数", 410);
        }

        return $next($request);
    }
}
