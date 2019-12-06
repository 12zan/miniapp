<?php

namespace SeZan\Kdniao;

use SeZan\Kernel\Http\HttpClient;

/**
 *
 * 快递鸟电子面单接口
 *
 * @技术QQ群: 340378554
 * @see: http://kdniao.com/api-eorder
 * @copyright: 深圳市快金数据技术服务有限公司
 *
 * ID和Key请到官网申请：http://kdniao.com/reg
 */


class Application
{

    const EBusinessID = 1361747;
    const AppKey      = '6ce385cb-eb88-4a4b-97a2-7286f0454376';
    //请求url，正式环境地址：http://api.kdniao.cc/api/Eorderservice    测试环境地址：http://testapi.kdniao.cc:8081/api/EOrderService
    const ReqURL      = 'http://api.kdniao.cc/api/Eorderservice';

    public function __construct()
    {
        # code...
    }

    public function createEOrder()
    {
        $client = new HttpClient();

        $requestData = $this->parseRequestData();

        $requestData = json_encode($requestData, JSON_UNESCAPED_UNICODE);

        $datas = array(
            'EBusinessID' => static::EBusinessID,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType'    => '2'
        );

        $datas['DataSign'] = $this->encrypt($requestData);

        $result= $client->post(static::ReqURL, $datas);

         var_export($result->getResponse());
    }

    //创建电子面单
    public function parseRequestData()
    {
        //构造电子面单提交信息
        $eorder = [];
        $eorder["ShipperCode"] = "HHTT";
        $eorder["OrderCode"] = "012657700387";
        $eorder["PayType"] = 1;
        $eorder["ExpType"] = 1;

        $sender = [];
        $sender["Name"] = "李先生";
        $sender["Mobile"] = "18888888888";
        $sender["ProvinceName"] = "广东省";
        $sender["CityName"] = "深圳市";
        $sender["ExpAreaName"] = "福田区";
        $sender["Address"] = "赛格广场5401AB";

        $receiver = [];
        $receiver["Name"] = "李先生";
        $receiver["Mobile"] = "18888888888";
        $receiver["ProvinceName"] = "广东省";
        $receiver["CityName"] = "深圳市";
        $receiver["ExpAreaName"] = "福田区";
        $receiver["Address"] = "赛格广场5401AB";

        $commodityOne = [];
        $commodityOne["GoodsName"] = "其他";
        $commodity = [];
        $commodity[] = $commodityOne;

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;

        return $eorder;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @return DataSign签名
     */
    function encrypt($data) {
        return urlencode(base64_encode(md5($data.static::AppKey)));
    }
}

?>