<?php

namespace App\Listeners;

use App\Events\CollectLogsEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Util\HttpClient;
use App\Repositories\ShopSetRepository;
use App\Jobs\Promotion;

class CollectLogs implements ShouldQueue
{

    const FENXI_URL = "https://t.z.12zan.net/v1/event";
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CollectLogsEvent  $event
     * @return void
     */
    public function handle(CollectLogsEvent $event)
    {
        $proStatus = app(ShopSetRepository::class)->promtion($event->data['rid']);

        //如果关掉了邀请充值奖励
        if (!$proStatus['status']) {
            return true;
        }
        //如果是支付订单的事件，处理，计算商品佣金
        if ($event->data['eventType'] == 'payOrder') {

            $orderSn = $event->data['orderSn'];
            $totalPay = $event->data['totalPay'];
            $promotion = $totalPay * $proStatus['percent'];

            $str = 'sn:'.$orderSn.';money:'.$totalPay.';promotion:'.$promotion;

            $event->data['eventData'] = $str;

        }

        $event->data['rn'] = md5(microtime(true));

        (new HttpClient)->getQuery(self::FENXI_URL, $event->data);
        //计算返利
        Promotion::dispatch([
            'rn'        => $event->data['rn'],
            'encOpenId' => $event->data['encOpenId']
        ]);
    }
}
