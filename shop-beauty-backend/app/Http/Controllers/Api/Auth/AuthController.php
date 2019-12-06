<?php

namespace App\Http\Controllers\Api\Auth;

use App\Biz\Salt;
use App\Biz\Jwt;
use App\Http\Controllers\Controller;
use App\Repositories\AppRepository;
use App\Repositories\WxUserRepository;
use App\Repositories\MemberRepository;
use EasyWeChat\Factory as EwFactory;

class AuthController extends Controller
{
    protected $mapp;
    protected $wxUser;
    private   $member;

    public function __construct(
        AppRepository    $mapp,
        WxUserRepository $wxUser,
        MemberRepository $member
    ){
        $this->mapp   = $mapp;
        $this->wxUser = $wxUser;
        $this->member = $member;
    }

    public function login()
    {
        $request = request();

        $this->validate($request, [
            'code' => 'required|string',
            'appid' => 'required|string'
        ]);

        $appid = $request->input('appid');
        $code  = $request->input('code');

        $appConfig = $this->mapp->findOrFailByAppid($appid);

        $config = [
            'app_id' => $appid,
            'secret' => $appConfig->appSecret
        ];

        $miniProgram = EwFactory::miniProgram($config);

        $res = $miniProgram->auth->session($code);

        if( isset($res["errcode"]) && $res["errcode"] > 0 ) {
            \Log::error('wechat login error info'.$res);
            throw new \ApiCustomException("小程序配置信息不正确，请联系店主!", 404);
        }

        $has = $this->wxUser->hasThisOpenid($res['openid']);

        \Cache::put('bty_'.$res['openid'].'_sn_key', $res['session_key'], 4*60);

        if (!$has) {
            $data['open_id'] = $res['openid'];
            $data['appid']   = $appid;
            $data['rid']     = $appConfig->rid;
            $openid          = $res['openid'];
            $data['union_id'] = isset($res['unionid']) ? $res['unionid'] : '';

            \DB::transaction(function () use ($data, $appConfig, $openid) {
                $this->wxUser->store($data);

                $rData = [
                    'rid' => $appConfig->rid
                ];
                $this->member->store($openid, $rData);
            }, 2);
        }

        $data = $this->wxUser->findOrFailByOpenid($res['openid']);

        $token = (new Jwt)->getTokenById($data->only(['id', 'open_id', 'appid', 'rid']));

        return $this->respondWithToken(
            $token,
            Salt::hash4OpenId($res["openid"])
        );
    }

    //微信用户同意授权，获取相关信息
    public function fixInfo()
    {
        $request = request();

        $this->validate($request, [
            'userInfo' => 'required'
        ]);

        $openid = app('sauth')->getAuthUserOpenId();
        $rid    = app('sauth')->getRid();
        // $open_id = 'oBbFG4yYOp05CvySvbrexHtGlG5U';

        $user = $this->wxUser->findOrFailByOpenid($openid);

        $user->nickname = $request->input('userInfo.nickName');
        $user->avatar   = $request->input('userInfo.avatarUrl');
        $user->gender   = $request->input('userInfo.gender') == 1 ? 'M' : ($request->input('userInfo.gender') == 2 ? 'W' : 'N');
        $user->city     = $request->input('userInfo.city');
        $user->province = $request->input('userInfo.province');
        $user->country  = $request->input('userInfo.country');
        $user->language = $request->input('userInfo.language');
        $user->union_id = $request->input('userInfo.unionId', '');

        $user->save();

        return $this->responseJson('ok');
    }

    public function fixPhone()
    {
        $request = request();

        $this->validate($request, [
            'phone' => 'required',
            'iv'    => 'required'
        ]);

        $rid    = app('sauth')->getRid();
        $openid = app('sauth')->getAuthUserOpenId();
        $phone  = $request->phone;
        $iv     = $request->iv;

        $appConfig = $this->mapp->findOrFailByRid($rid);

        $config = [
            'app_id' => $appConfig->appId,
            'secret' => $appConfig->appSecret
        ];

        $app = EwFactory::miniProgram($config);

        $sessionKey = \Cache::get('bty_'.$openid.'_sn_key');

        $decryptedData = $app->encryptor->decryptData($sessionKey, $iv, $phone);

        $phone = $decryptedData['purePhoneNumber'];

        $this->member->update($openid, ['phone' => $phone]);

        return $this->responseJson(['phone' => $phone]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @param  string $encOpenId
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $encOpenId="")
    {
        return $this->responseJson([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            "encOpenId"    => $encOpenId
        ]);
    }
}