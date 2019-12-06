<?php

namespace App\Services;

use App\Repositories\ActivityRepository;
use App\Repositories\OrderRepository;
use App\Repositories\MemberRepository;
use App\Repositories\MemberMoneyLogRepository;
use App\Repositories\ServerItemRepository;

class StoreValueSend
{

    public static function handle($orderId, $openid)
    {
        $order = app(OrderRepository::class)->findOrFailById($orderId, $openid);

        $rid       = $order->rid;
        $condition = $order->settlement_amount;
        $appid     = $order->appid;

        $row = app(ActivityRepository::class)->query()->where([
            'rid'    => $rid,
            'status' => 1,
            'type'   => '0301'
        ])->where('time_start', '<', app('carbon')->now()->format('Y-m-d H:i:s'))
        ->where('time_end', '>', app('carbon')->now()->format('Y-m-d H:i:s'))
        ->orderBy('time_type', 'desc')
        ->first();



        if (empty($row)) {
           return true;
        }

        $rows = $row->recharge()
        ->where('condition', '<=', $condition * 100)
        ->with('gifts')
        ->orderBy('condition', 'desc')
        ->first();

        $rows->gifts->each(function ($item) use ($openid, $rid, $appid, $row) {
            if($item->type == 1) { //赠送金额
                self::sendMoney($openid, $rid, $item->price, $row->name);
            }elseif($item->type == 2) { //赠送服务
                self::sendServers($openid, $rid, $appid, $item->relation_id, $item->num);
            }
        });

        return true;
    }

    //赠送金额
    private static function sendMoney($openid, $rid, $money, $activityName = null)
    {
        $distMoney = app(MemberRepository::class)->incrMoney($openid, $money, 'gift');
        //记录日志
        $data = [
            'rid'        => $rid,
            'code'       => app('enums')->moneyLogType()->withType('STORE_SEND')->getCode(),
            'money'      => $money,
            'dist_money' => $distMoney,
            'type'       => 'in',
            'remark'     => "参与\"".$activityName."\"活动赠送"
        ];
        app(MemberMoneyLogRepository::class)->store($openid, $data);
    }
    //赠送服务
    private  static function sendServers($openid, $rid, $appid, $serverId, $count)
    {

        $payMethod = app('enums')->payMethod()->withType('STORE_SEND')->getCode();
        $phone = app(MemberRepository::class)->getPhone($openid);

        $data['appid']             = $appid;
        $data['price']             = 0;
        $data['settlement_amount'] = 0;
        $data['remark']            = '活动赠送';
        $data['rid']               = $rid;
        $data['type']              = app('enums')->orderType()->withType('STORE_SEND')->getCode();
        $data['extro_info']        = json_encode([
                                        'phone'     => $phone,
                                        'payMethod' => $payMethod,
                                        'priceType' => ''
                                    ]);
        $data['status']            = 2;
        $data['paid_at']           = app('carbon')->now();

        $goods = app(ServerItemRepository::class)->findById($serverId, $rid);

        \DB::transaction(function () use ($data, $openid, $goods, $count, $rid) {
            //保存订单
            $order = app(OrderRepository::class)->store($openid, $data);

            $orderId = $order->id;
            //保存订单关联的商品
            $orderGoods = [
                'server_id'    => $goods->id,
                'count'        => $count,
                'price'        => 0,
                'name'         => $goods->name,
                'server_price' => $goods->price,
                'info'         => $goods->info ? json_encode($goods->info) : '',
                'details'      => $goods->details ? json_encode($goods->details) : '',
                'image'        => $goods->cover->image ? $goods->cover->image->path : ''
            ];
            app(OrderRepository::class)->storeOrderServers($orderId, $orderGoods);

            app(OrderRepository::class)->storeOrderServerInfo($orderId, $rid);

            app(ServerItemRepository::class)->query()->where('id', $goods->id)->increment('sends', $count);

        }, 2);

        return true;

    }


}