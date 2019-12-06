<?php

namespace App\Biz;

use App\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Illuminate\Http\Request;

class Jwt
{

    const SIGKEY = 'NoSaltIsSafeUntilYouRemoveAll';

    public function getTokenById($data)
    {
        return  $this->builder($data)->getToken()->__toString();
    }

    protected function builder($claims = [])
    {
         $signer = new Sha256();

         $obj = (new Builder())->setIssuer('http://www.12zan.cn') // Configures the issuer (iss claim)
             ->setAudience('http://wwww.12zan.cn') // Configures the audience (aud claim)
             ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
             ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
             // ->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
             ->setExpiration(time() + 3600*4); // Configures the expiration time of the token (exp claim)

        foreach ($claims as $key => $value) {
            $obj = $obj->set($key, $value);
        }

        $obj->sign($signer, self::SIGKEY);   // creates a signature using "testing" as key

        return $obj;
    }

    public function getClaim(Request $request, $claim)
    {
        $token = $this->getTokenFromRequest($request);

        return  $this->parseToken($token)->getClaim($claim);
    }

    public function getClaims(Request $request)
    {
        $token = $this->getTokenFromRequest($request);

        return  $this->parseToken($token)->getClaims();
    }


    public function auth($request)
    {
        $this->isVerify($request);
        $this->isExpired($request);
    }

    //是否过期
    protected function isExpired($request)
    {
        $token = $this->getTokenFromRequest($request);

        if ($this->parseToken($token)->isExpired()) {
             throw new \ApiCustomException("token 过期", 401);
        }
    }

    //是否合法
    protected function isVerify($request)
    {
        $signer = new Sha256();

        $token = $this->getTokenFromRequest($request);

        if (!$this->parseToken($token)->verify($signer, self::SIGKEY)) {
           throw new \ApiCustomException("token 非法", 401);
        }
    }

    protected function parseToken($token)
    {
        try {
             $token = (new Parser())->parse((string) $token); // Parses from a string
        } catch (\Exception $e) {
            throw new \ApiCustomException("无效 token", 401);
        }

        return $token;
    }

    protected function getTokenFromRequest($request)
    {
        $token = $request->bearerToken();

        if (!$token) {
             throw new \ApiCustomException("缺少token值", 401);
        }

        return $token;
    }

}
