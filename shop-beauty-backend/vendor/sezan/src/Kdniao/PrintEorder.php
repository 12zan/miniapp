<?php

namespace SeZan\Kdniao;

use SeZan\Kernel\Http\HttpClient;
use SeZan\Kernel\Exceptions\KdNiaoException;
use SeZan\Kernel\Support\Str;

class PrintEorder
{

    public          $EBusinessID;
    public          $AppKey     ;
    public          $requestData;
    protected       $logisticCode;
    protected       $express;
    protected       $isPriview = '1'; //是否预览，0-不预览 1-预览
    const           ReqURL         = 'http://www.kdniao.com/External/PrintOrder.aspx';
    const           IP_SERVICE_URL = 'http://www.kdniao.com/External/GetIp.aspx';

    public function __construct($ebusinessId, $AppKey)
    {
        $this->EBusinessID = $ebusinessId;
        $this->AppKey      = $AppKey;
        $this->httpClient  = new HttpClient();
        $this->eorder      = new Eorder($ebusinessId, $AppKey);
    }

    /**
     * 组装POST表单用于调用快递鸟批量打印接口页面
     */
    public function build() {
        //OrderCode:需要打印的订单号，和调用快递鸟电子面单的订单号一致，PortName：本地打印机名称，
        //请参考使用手册设置打印机名称。支持多打印机同时打印。

        $request_data = $this->getRequestData();

        $data_sign = $this->encrypt($this->getIp().$request_data);

        //组装表单
        $form = '<form id="form1" method="POST" action="'.static::ReqURL.'">
        <input type="text" name="RequestData" value="'.urlencode($request_data).'"/>
        <input type="text" name="EBusinessID" value="'.$this->getEBusinessID().'"/>
        <input type="text" name="DataSign" value="'.$data_sign.'"/>
        <input type="text" name="IsPriview" value="'.$this->isPriview.'"/>
        </form><script>form1.submit();</script>';

        return ['html' => $form, 'logisticCode' => $this->logisticCode, 'express' => $this->express];
    }

    protected function getRequestData()
    {
        if (empty($this->requestData)) {
            throw new KdNiaoException('缺少快递单信息');
        }

        return $this->requestData;
    }

    public function parseRequestData($eorder, $sender, $receiver, $commodityOne = null)
    {
        $orderArr = $this->eorder->parseRequestData($eorder, $sender, $receiver, $commodityOne)->send(false);

        $data = [
            ['OrderCode' => $orderArr['Order']['OrderCode'], 'PortName' => '']
        ];

        $this->requestData  = json_encode($data);
        $this->logisticCode = $orderArr['Order']['LogisticCode'];
        $this->express      = $orderArr['express'];

        return $this;
    }

    /**
     * 判断是否为内网IP
     * @param ip IP
     * @return 是否内网IP
     */
    protected function is_private_ip($ip) {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * 获取客户端IP(非用户服务器IP)
     * @return 客户端IP
     */
    protected function getIp() {
        //获取客户端IP
        $ip = '127.0.0.1';

        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if(!$ip || $this->is_private_ip($ip)) {

            $res = $this->httpClient->get(static::IP_SERVICE_URL);

           return  trim(Str::substr($res->getResponse(), 0, 15));

        } else {
            return $ip;
        }
    }

    public function getEBusinessID()
    {
        return $this->EBusinessID;
    }

    public function getAppKey()
    {
        return $this->AppKey;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @return DataSign签名
     */
    protected function encrypt($data) {
        return urlencode(base64_encode(md5($data . $this->getAppKey())));
    }

}

?>
