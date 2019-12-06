<?php

namespace App\Biz;

use Carbon\Carbon;

class Enums
{

    public static $data;
    public $type;
    public $typeValue;

    //二维码类型
    public static function qrCodeType()
    {
        $ret = [
            ['code' => 'pay', 'name' => '付款码', 'type' => 'PAY'],
            ['code' => 'exchange', 'name' => '兑换码', 'type' => 'EXCHANGE']
        ];

        self::$data = $ret;

        return new self;
    }

    //订单类型
    public static function orderType()
    {
        $ret = [
            ['code' => '0301', 'name' => '购买服务', 'type' => 'BUY_SERVER'],
            ['code' => '0302', 'name' => '充值', 'type' => 'STORE'],
            ['code' => '0303', 'name' => '活动赠送', 'type' => 'STORE_SEND']
        ];

        self::$data = $ret;

        return new self;
    }

    //订单状态
    public static function orderStatus()
    {
        $ret = [
            ['code' => 1, 'name' => '未支付', 'type' => 'UNPAY'],
            ['code' => 2, 'name' => '已支付', 'type' => 'PAID'],
            ['code' => 3, 'name' => '已发货', 'type' => 'SENDING'],
            ['code' => 4, 'name' => '确认收货', 'type' => 'SURE'],
            ['code' => 5, 'name' => '已完成', 'type' => 'FINISH'],
            ['code' => -1, 'name' => '已失效', 'type' => 'TIMEOUT'],
            ['code' => -2, 'name' => '已取消', 'type' => 'CANCLE'],
            ['code' => -9, 'name' => '已退款', 'type' => 'REFOUND'],
        ];

        self::$data = $ret;

        return new self;
    }

    //价格类型
    public static function priceType()
    {
        $ret = [
            ['code' => 1, 'name' => '会员价', 'type' => 'MEMBER'],
            ['code' => 2, 'name' => '非会员价', 'type' => 'UNMEMBER'],
        ];

        self::$data = $ret;

        return new self;
    }

    //余额变更类型
    public static function moneyLogType()
    {
        $ret = [
            ['code' => 1, 'name' => '微信充值', 'type' => 'WXPAY'],
            ['code' => 2, 'name' => '充值返送', 'type' => 'STORE_SEND'],
            ['code' => 3, 'name' => '退款', 'type' => 'REFOUND'],
            ['code' => 4, 'name' => '购买服务', 'type' => 'BUY_SEVER'],
            ['code' => 5, 'name' => '扫码扣款', 'type' => 'QR_DES'],
            ['code' => 6, 'name' => '后台手工操作', 'type' => 'PEO_HANDLE'],
            ['code' => 7, 'name' => '邀请奖励金', 'type' => 'PEO_HANDLE']
        ];

        self::$data = $ret;

        return new self;
    }

    //支付方式
    public static function payMethod()
    {
        $ret = [
            ['code' => 1, 'name' => '微信支付', 'type' => 'WXPAY'],//微信支付
            ['code' => 2, 'name' => '余额支付', 'type' => 'WALLET'],//余额支付
            ['code' => 3, 'name' => '活动赠送', 'type' => 'STORE_SEND']//储值赠送
        ];

        self::$data = $ret;

        return new self;
    }

    public function withType($value)
    {
        $this->type = 'type';
        $this->typeValue = $value;

        return $this;
    }

    public function withCode($value)
    {
        $this->type = 'code';
        $this->typeValue = $value;

        return $this;
    }

    public function getName()
    {
        $ret = $this->getRow();

        if (empty($ret)) {
            return '未知';
        }

        return $ret['name'];
    }

    public function getCode()
    {
        $ret = $this->getRow();

        if (empty($ret)) {
            return '未知';
        }

        return $ret['code'];
    }

    public function getRow()
    {
        $collect = collect(self::$data);

        $row = $collect->where($this->type, $this->typeValue)->first();

        return $row;
    }

    public function getAll()
    {
        return self::$data;
    }
}