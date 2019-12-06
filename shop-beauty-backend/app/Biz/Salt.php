<?php

namespace App\Biz;

use Carbon\Carbon;

class Salt
{
    const SaltOfOpenId="NoSaltIsSafeUntilYouRemoveAll";

    public static function hash4OpenId($value) {
        return md5(self::SaltOfOpenId.$value);
    }

    //xml to array
    public static function xml2Array($xmlstring)
    {
        $xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        return $array;
    }

    //截取中文字符串
    public static function subStr($str, $length)
    {
        $strLength = mb_strlen($str);

        if ($strLength > $length) {
            return mb_substr($str, 0, $length).'...';
        } else {
            return $str;
        }
    }

    public static function isHttps()
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return true;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return true;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return true;
        }

        return false;
    }

    public static function wxPayMakeSign($prepayID, $config = [])
    {
        $timestamp = time();
        $noneStr = str_random(random_int(16, 32));
        $template = "appId=%s&nonceStr=%s&package=%s&signType=MD5&timeStamp=%s&key=%s";
        $str = sprintf(
            $template,
            $config['app_id'],
            $noneStr,
            "prepay_id=$prepayID",
            $timestamp,
            $config['key']
        );

        $paySign = md5($str);

        return [
            "timestamp" => "$timestamp",
            "prepay_id" => $prepayID,
            "nonce_str" => $noneStr,
            "sign"      => $paySign,
        ];
    }
}
