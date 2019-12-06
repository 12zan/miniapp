<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Image;
use App\Models\Gallery;
use App\Models\PriceList;
use App\Models\ShopAddress;

class ShopSetRepository extends BaseRepository
{

    public function getShopInfo($rid)
    {
        $data = \DB::table('shop_info')->where('rid', $rid)->first();

        if (empty($data)) {
            \Log::error('未查询店铺设置', ['rid' => $rid]);
            throw new \ApiCustomException("未查询店铺设置", 404);
        }

        return $data;
    }

    public function getShopInfos($rid)
    {
        $info = $this->getShopInfo($rid);

        $logo = Image::where('id', $info->logo_id)->value('path');
        $address = ShopAddress::where('id', $info->shop_address_id)->first();

        if ($address) {
            $lng = $address->lng;
            $lat = $address->lat;
            $address = $address->city.$address->county.$address->address;
        }
        else{
            $address = '';
            $lng = 1;
            $lat = 1;
        }

        $data = [
            'logo'    => $logo,
            'address' => $address,
            'lng'     => $lng,
            'lat'     => $lat,
            'phone'   => $info->phone,
            'name'    => $info->name,
            'des'     => $info->name_s,
            'openTime' => $this->getOpenTime($rid)
        ];

        return $data;
    }

    //首页banner
    public function getBanners($rid)
    {
        $list = Gallery::query()->where([
            'rid'    => $rid,
            'status' => 1
        ])->with('image')->get();

        return $list;
    }
    //获取价目表
    public function getPriceList($rid)
    {
        $list = PriceList::query()->where('rid', $rid)->get();

        return $list;
    }

    //会员以会员价结算的时候，是否可以使用微信支付
    public function memberMustUseWxPay($rid, $isThrow = true)
    {
        $info = $this->getShopInfo($rid);

        $ret = true;
        if (empty($info->extension)){
            $ret = false;
        }

        $ret = json_decode($info->extension, true);
        if (!isset($ret['member'])) {
            $ret = false;
        }

        if ($ret['member']['type'] == 2) {
            $ret = false;
        }

        if (!$ret) {
            if ($isThrow) {
                throw new \ApiCustomException('会员以会员价结算的时候，必须使用余额支付', 410);
            }
            return $ret;
        }

        return true;
    }

    //最小充值金额
    public function getMinRecharge($rid)
    {
        $info = $this->getShopInfo($rid);

        $ret = 0;
        if (empty($info->extension)){
            $ret = 0;
        }

        $ret = json_decode($info->extension, true);
        if (!isset($ret['recharge_min'])) {
            $ret = 0;
        }else{
            $ret = $ret['recharge_min'];
        }

        return $ret;
    }

    public function getStaffOrderInfo($rid)
    {
        $info = $this->getShopInfo($rid);

        $ret = ['status' => false, 'openTime' => ['start' => '00:00', 'end' => '24:00']];
        if (empty($info->extension)){
            return $ret;
        }

        $ret = json_decode($info->extension, true);
        if (!isset($ret['staff_order'])) {
            return $ret;
        }else{
            $ret['status']            = $ret['staff_order']['status'] == 1 ? true : false;
            $ret['openTime']['start'] = $ret['staff_order']['start_at'];
            $ret['openTime']['end']   = $ret['staff_order']['end_at'];

            return $ret;
        }

    }

    public function getOpenTime($rid)
    {
        $info = $this->getShopInfo($rid);

        if (empty($info->extension)){
            return '全天24小时';
        }

        $ret = json_decode($info->extension, true);
        if (isset($ret['open_time'])) {
            return isset($ret['open_time']['text']) ? $ret['open_time']['text'] : '全天24小时';
        }else{
            return '全天24小时';
        }
    }

    //邀请链接设置
    public function promtion($rid)
    {
        $info = $this->getShopInfo($rid);

        $ret = ['status' => false, 'percent' => 0];
        if (empty($info->extension)){
            return $ret;
        }

        $ret = json_decode($info->extension, true);
        if (!isset($ret['invite'])) {
            $ret = ['status' => false, 'percent' => 0];
        }

        if ($ret['invite']['status'] == 1) {
            $ret['status'] = true;
            $ret['percent'] = $ret['invite']['percent'];
        }else{
            $ret['status'] = false;
            $ret['percent'] = $ret['invite']['percent'];
        }


        return $ret;
    }


}