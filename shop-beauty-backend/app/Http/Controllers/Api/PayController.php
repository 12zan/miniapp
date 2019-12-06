<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Repositories\AppRepository;
use App\Repositories\MemberRepository;
use App\Repositories\MemberMoneyLogRepository;
use App\Repositories\ServerItemRepository;
use App\Models\Payment;
use App\Events\CollectLogsEvent;
use EasyWeChat\Factory;
use App\Jobs\SendMessage;
use App\Services\StoreValueSend;

class PayController extends Controller
{
    private $order;
    private $mapp;
    private $member;

    public function __construct(
        OrderRepository $order,
        AppRepository $mapp,
        MemberRepository $member
    )
    {
        $this->order = $order;
        $this->mapp  = $mapp;
        $this->member = $member;
    }

    //微信支付
    public function wxPay()
    {
        $request = request();

        $this->validate($request, [
            'orderId' => 'required|numeric'
        ]);

        $orderId = $request->orderId;
        $rid     = app('sauth')->getRid();
        $appid   = app('sauth')->getAppId();
        $openid  = app('sauth')->getAuthUserOpenId();

        $order = $this->order->isPaied($orderId, $openid);

        $config = $this->mapp->getWxPayConfig($rid, $appid);

        $payment = Factory::payment($config); //微信支付

        try {
            $result = $payment->order->unify([
                'body'         => '商品',
                'out_trade_no' => $order->wx_order_sn,
                'total_fee'    => $order->settlement_amount * 100,
                'trade_type'   => 'JSAPI',
                'openid'       => $openid
            ]);
        } catch (\Exception $e) {
            \Log::error('wechat pay create order 1 error : '.$e->getMessage());
            throw new \ApiCustomException("微信支付异常,请联系店主!", 500);
        }

        if (isset($result['return_code']) && isset($result['return_msg'])
            && ($result['return_code'] == "SUCCESS") && ($result['return_msg'] == "OK")
            && isset($result['result_code']) && ($result['result_code'] == "SUCCESS")
        ) {
            return app('msalt')->wxPayMakeSign($result['prepay_id'], $config);
        }
            \Log::error(' wechat pay create order 2 result :', $result);
            throw new \ApiCustomException("微信支付异常,请联系店主!", 500);
    }

    //余额支付
    public function storePay()
    {
        $request = request();

        $this->validate($request, [
            'orderId' => 'required|numeric'
        ]);

        $orderId = $request->orderId;
        $rid     = app('sauth')->getRid();
        $appid   = app('sauth')->getAppId();
        $openid  = app('sauth')->getAuthUserOpenId();

        $order = $this->order->isPaied($orderId, $openid);

        $member = $this->member->hasMoney($openid, $order->settlement_amount);

         \DB::transaction(function () use ($order, $member, $rid) {
            //更新订单
            $order->paid_at    = date('Y-m-d H:i:s'); // 更新支付时间为当前时间
            $order->status     = 2;
            $order->pay_amount = $order->settlement_amount;
            $this->handleOrder($order, 'WALLET');
            $order->save();
            //减余额
            $distMoney = $this->member->descMoney($member->openid, $order->settlement_amount);
            //记录日志
            $data = [
                'rid'        => $rid,
                'code'       => app('enums')->moneyLogType()->withType('BUY_SEVER')->getCode(),
                'money'      => $order->settlement_amount,
                'dist_money' => $distMoney,
                'type'       => 'out',
                'remark'     => $order->servers->name.'(数量：'.$order->servers->count.')'
            ];
            app(MemberMoneyLogRepository::class)->store($member->openid, $data);

        }, 2);

         return $this->responseJson('ok');
    }

    //微信支付，异步通知
    public function notify()
    {
        $request = request();

        $wechatData = app('msalt')->xml2Array($request->getContent());
        $appConfig  = $this->mapp->findOrFailByAppid($wechatData['appid']);
        $config     = $this->mapp->getWxPayConfig($appConfig->rid, $appConfig->appId);

        $payment = Factory::payment($config); //微信支付

        libxml_disable_entity_loader(true);

        $response = $payment->handlePaidNotify(function ($message, $fail) use ($appConfig) {
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单

            $order = $this->order->findByWxOrderSn($message['out_trade_no']);

            if ($order->status == 2) { //订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    try {
                        \DB::transaction(function () use ($order, $message) {
                            //保存支付数据
                            Payment::create([
                                'order_id'       => $order->id,
                                'wx_order_sn'    => $message['out_trade_no'],
                                'money'          => $message['cash_fee'],
                                'transaction_id' => $message['transaction_id'],
                                'rid'            => $order->rid,
                                'notify_info'    => json_encode($message)
                            ]);
                            //更新订单
                            $order->paid_at    = date('Y-m-d H:i:s'); // 更新支付时间为当前时间
                            $order->status     = 2;
                            $order->pay_amount = $message['cash_fee'] / 100;
                            $this->handleOrder($order, 'WXPAY');
                            $order->save();

                        }, 2);

                        $this->sendMessage($order);
                    } catch (\Exception $e) {
                        \Log::error(' wechat pay notice error,  wx_order_sn is :' . $message['out_trade_no'],
                             ['msg' => $e->getMessage()]);
                        return $fail('通信失败，请稍后再通知我');
                    }
                }
             } else {
                 return $fail('通信失败，请稍后再通知我');
             }

            return true; // 返回处理完成
        });
        return $response;
    }

    //发送消息
    protected function sendMessage($order)
    {
        return true;
    }

    protected function handleOrder(&$order, $payType)
    {
        //如果是储值订单
        if ($order->type == app('enums')->orderType()->withType('STORE')->getCode()) {
            $distMoney = $this->member->incrMoney($order->open_id, $order->price);
            $this->member->setMemberLevel($order->open_id, 1); //设置会员

            //记录日志
            $data = [
                'rid'        => $order->rid,
                'code'       => app('enums')->moneyLogType()->withType('WXPAY')->getCode(),
                'money'      => $order->price,
                'dist_money' => $distMoney,
                'type'       => 'in',
                'remark'     => '微信充值'
            ];
            app(MemberMoneyLogRepository::class)->store($order->open_id, $data);
            //储值赠送
            StoreValueSend::handle($order->id, $order->open_id);

            //记录行为日志
            $eventData = [
                'rid'          => $order->rid,
                'appId'        => $order->appid,
                'eventType'    => 'payOrder',
                'sid'          => '',
                'encOpenId'    => app('msalt')->hash4OpenId($order->open_id),
                'orderId'      => $order->id,
                'orderSn'      => $order->order_sn,
                'totalPay'     => $order->price * 100,
                'eventDataNum' => 1
            ];

            event(new CollectLogsEvent($eventData));
        }
        //如果是购买服务订单
        if ($order->type == app('enums')->orderType()->withType('BUY_SERVER')->getCode()) {
            $serverId = $this->order->storeOrderServerInfo($order->id, $order->rid);
            app(ServerItemRepository::class)->query()->where('id', $serverId)->increment('sales');
        }

        return true;
    }



}