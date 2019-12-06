<?php

namespace App\Biz;

use Illuminate\Http\Request;
use App\Repositories\AppRepository;
use App\Repositories\WxUserRepository;
use Lcobucci\JWT\Claim\Factory;

class Auth
{

    public function getAuthPayload()
    {
        $jwt = new Jwt;
        $request = request();


        return $jwt->getClaims($request);
    }

    public function getAppId()
    {
        $appid = $this->getAuthPayload()['appid']->getValue();

        return $appid;
    }

    public function getRid()
    {
        $rid = $this->getAuthPayload()['rid']->getValue();

        return $rid;
    }

    public function getSid()
    {
        $appConfig  = app(AppRepository::class)->findOrFailByAppid($this->getAppId);

        return $appConfig->sid;
    }

    public function getAuthUserOpenId()
    {
        $openid = $this->getAuthPayload()['open_id']->getValue();

        return $openid;
    }

        //未登录情况获取rid
    public function getRidWhenUnauth()
    {
        $request = request();

        $appid = $request->header('appid');
        $appConfig = app(AppRepository::class)->findOrFailByAppid($appid);

        return $appConfig->rid;
    }

}
