<?php

namespace SeZan\Feie;

use SeZan\Kernel\Http\HttpClient;
use SeZan\Kernel\Exceptions\CustomeException;

abstract class Feie
{

    public          $user;
    public          $ukey;
    public          $sn;
    public          $time;
    const           ReqURL = 'api.feieyun.cn/Api/Open/';


    public function __construct($user, $ukey, $sn = null)
    {
        $this->user = $user;
        $this->ukey = $ukey;
        $this->sn   = $sn;
        $this->time = time();
        $this->client = new HttpClient();
    }

    public function pushParams($privateParams = [])
    {
        $content = [
            'user'           => $this->user,
            'stime'          => $this->time,
            'sig'            => $this->getStime(),
            'apiname'        => $this->getApiName()
        ];

        return array_merge($content, $privateParams);
    }

    public function validate()
    {
        # code...
    }


    abstract protected function getApiName();

    protected function getStime() {
        return sha1($this->user.$this->ukey.$this->time);
    }

    public function send($err_open = true)
    {
        $content  = $this->pushParams($this->privateParams);

        $result = $this->client->post(self::ReqURL, $content);

        $res = $result->getResponse();
        $err_open and $this->handError($res);

        return json_decode($res, true);

    }

    protected function handError ($ret)
    {
        $ret = json_decode($ret, true);

        if ($ret['ret'] !== 0) {
            throw new CustomeException($ret['msg'], 505);
        } elseif(($ret['ret'] === 0) && !empty($ret['data']['no'])) {
            throw new CustomeException($ret['data']['no'][0], 505);
        }

        return true;
    }

}

?>