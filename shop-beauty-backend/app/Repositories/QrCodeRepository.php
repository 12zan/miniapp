<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class QrCodeRepository extends BaseRepository
{

    private $saveTime = 1; //分钟

    //付款码
    public function createPayCode($openid)
    {
        $code = md5($openid.microtime(true));

        \Cache::put($code, $openid, $this->saveTime);

        return $code;
    }

    //删除付款码
    public function delPayCode($code)
    {
        \Cache::forget($code);

        return true;
    }

    //检验付款码是否失效
    public function payCodeIsAvaiable($code, $isThrow = true)
    {
        $value = \Cache::get($code);

        if (empty($value)) {
            if ($isThrow) {
                throw new \ApiCustomException('二维码失效', 410);
            }
            return ['msg' => '二维码失效', 'status' => false];
        }

        return ['code' => $value, 'status' => true];
    }

}