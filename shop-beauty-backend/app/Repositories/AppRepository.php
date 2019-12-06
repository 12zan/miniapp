<?php

namespace App\Repositories;

use App\Models\AppConfig;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppRepository extends BaseRepository
{
    const MODEL = AppConfig::class;

    public function findOrFailByAppid($appid)
    {
        try {
            return $this->query()->where('appId', $appid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            \Log::error('未查询到小程序配置', ['appid' => $appid]);
            throw new \ApiCustomException("未查询到小程序配置", 404);
        }
    }

    public function findOrFailByRid($rid)
    {
        try {
            return $this->query()->where('rid', $rid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            \Log::error('未查询到小程序配置', ['rid' => $rid]);
            throw new \ApiCustomException("未查询到小程序配置", 404);
        }
    }

    //获取微信支付配置
    public function getWxPayConfig($rid, $appid)
    {
        $wxPayConfig = \DB::table('wx_pay')->where('rid', $rid)->first();

        if (empty($wxPayConfig)) {
            throw new \ApiCustomException("商家还未配置支付方式,请咨询客服!", 500);
        }

        $config = [
            // 必要配置
            'app_id'             => $appid,
            'mch_id'             => $wxPayConfig->mch,
            'key'                => $wxPayConfig->key, // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            // 'cert_path'          => $wxPayConfig->cert_path, // FIX ME
            // 'key_path'           => $wxPayConfig->key_path,      // FIX ME

            'notify_url'         => 'https://beauty.z.12zan.net/api/pay/notify'
        ];

        return $config;
    }

}