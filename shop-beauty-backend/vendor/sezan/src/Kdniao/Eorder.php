<?php

namespace SeZan\Kdniao;

use SeZan\Kernel\Http\HttpClient;
use SeZan\Kernel\Exceptions\KdNiaoException;

class Eorder
{

    public $EBusinessID;
    public $AppKey;
    protected $requestData;
    public $express;
    //请求url，正式环境地址：http://api.kdniao.cc/api/Eorderservice
    // 测试环境地址：http://testapi.kdniao.cc:8081/api/EOrderService
    const ReqURL      = 'http://api.kdniao.cc/api/Eorderservice';
    // const ReqURL      = 'http://beiwen.z.12zan.net/api/';

    public function __construct($ebusinessId, $AppKey)
    {
        $this->EBusinessID = $ebusinessId;
        $this->AppKey      = $AppKey;
    }

    public function send($json = true)
    {
        $client = new HttpClient();

        $datas = $this->parseSendDatas();

        $result = $client->post(static::ReqURL, $datas);

        $response =  $result->getResponse();

        $this->handError($response);

        if ($json) {
            return $response;
        } else {
            return array_merge(json_decode($response, true), ['express' => $this->express]);
        }

    }

    protected function handError($data)
    {
        $data = json_decode($data, true);

        if (!$data['Success']) {
            throw new KdNiaoException($data['Reason']);
        }
    }

    public function parseSendDatas()
    {
        $requestData = $this->getRequestData();

        $requestData = json_encode($requestData, JSON_UNESCAPED_UNICODE);

        $datas = array(
            'EBusinessID' => $this->getEBusinessID(),
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType'    => '2'
        );

        $datas['DataSign'] = $this->encrypt($requestData);

        return $datas;
    }

    public function getEBusinessID()
    {
        return $this->EBusinessID;
    }

    public function getAppKey()
    {
        return $this->AppKey;
    }

    protected function getRequestData()
    {
        if (empty($this->requestData)) {
            throw new KdNiaoException('缺少快递单信息');
        }

        return $this->requestData;
    }

    //创建电子面单
    public function parseRequestData($eorder, $sender, $receiver, $commodityOne = null)
    {
        $this->express = $this->avaiableExpress($eorder['ShipperCode']);
        //构造电子面单提交信息
        $commodityOne["GoodsName"] = "商品";

        $commodity[] = $commodityOne;

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;

        $this->requestData = $eorder;

        return $this;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @return DataSign签名
     */
    public function encrypt($data) {
        return urlencode(base64_encode(md5($data.$this->getAppKey())));
    }

    /**
     * 第一批支持的直接打单的物流商
     */
    protected function avaiableExpress($key)
    {
        $able = [
            'FAST'    => '快捷快递',
            'SF'      => '顺丰',
            'UAPEX'   => '全一快递',
            'YZBK'    => '邮政国内标快',
            'YZPY'    => '邮政快递包裹',
            'ZJS'     => '宅急送',
            'ZTKY'    => '中铁快运'
        ];

        if (!isset($able[$key])) {
            throw new KdNiaoException('该快递公司，暂不支持直接打印电子面单，需要去开通账户');
        }

        return $able[$key];
    }
}

?>