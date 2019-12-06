<?php

namespace App\Services;

use SeZan\Feie\PrinterOrder;
use App\Repositories\OrderRepository;
use App\Repositories\PrinterRepository;
use App\Repositories\ShopSetRepository;
/**
 *
 */
class PrinterService
{

    public function handle($event)
    {
        $order = $event->order;
        $user = 'huanhuan@yuanli-inc.com';
        $key  = '2RBvnJPdHQubhRqU';
        $priter = new PrinterOrder($user, $key);

        $data = $this->parseType($order);

        //获取打印机
        $printerList = app(PrinterRepository::class)->getPrinter($order->rid);

        foreach ($printerList as $value) {
            $data['sn']    = $value['sn'];
            $data['times'] = $value['print_num'];

            if (env('APP_ENV') != 'local') {
                $priter->setParames($data)->send();
            }
        }
    }

    protected function parseType($order)
    {
        $data = [
            'orderInfo' => [
                'title'    => app(ShopSetRepository::class)->getShopName($order->rid),
                'order_sn' => $order->order_sn,
                'goods'    => $this->getOrderGoods($order),
                'count'    => $order->pay_amount,
                'remark'   => $order->remark
            ]
        ];
        switch ($order->type) {
            case '0201':
                $type = 1; //外卖模式
                $address = app(OrderRepository::class)->getAddressInfo($order->id);
                $data['orderInfo']['freight']        = $order->freight;
                $data['orderInfo']['address']        = $address->province.$address->city.$address->county;
                $data['orderInfo']['address_detail'] = $address->address;
                $data['orderInfo']['buyer']          = $address->name;
                $data['orderInfo']['phone']          = $address->phone;
                break;
            case '0202':
                $type = 3; //送餐到桌模式
                $data['orderInfo']['number_b'] = !empty($order->extro_info) ? $order->extro_info->tableNum : '';
                break;
            case '0203':
                $type = 2; //取餐号模式
                $data['orderInfo']['number_a'] = !empty($order->extro_info) ? $order->extro_info->number : '';
                break;
            default:
                $type = 2;
                break;
        }

        $data['orderInfo']['type'] = $type;
        return $data;
    }

    protected function getOrderGoods($order)
    {
        $data = app(OrderRepository::class)->getOrderGoods($order->id);
        $rdata = [];

        $data->each(function ($item) use (&$rdata) {
            $rdata[] = [
                'name'   => !empty($item->attr_spec) ? $item->goods_name.'('.$item->attr_spec.')' : $item->goods_name,
                'price'  => $item->price,
                'number' => $item->count
            ];
        });
        return $rdata;
    }
}