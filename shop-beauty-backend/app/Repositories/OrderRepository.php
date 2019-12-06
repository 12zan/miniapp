<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderServer;
use App\Models\OrderServerInfo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderRepository extends BaseRepository
{
    const MODEL = Order::class;

    public function findByIds($ids = [], $sort = [])
    {
        $query = $this->query()
            ->with('servers')
            ->whereIn('id', $ids);

        foreach ($sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get();
    }

    public function findOrFailById($id, $openid)
    {
        try {
            return $this->query()->where(['id' => $id, 'open_id' => $openid])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            \Log::error('未查询到该订单, order_id: '.$id);
            throw new \ApiCustomException("未查询到该订单", 404);
        }
    }

    public function getRowById($id, $openid)
    {
        $row = $this->findOrFailById($id, $openid);

        return $row->with('servers.infos')
            ->where(['id' => $id, 'open_id' => $openid])
            ->first();
    }

    public function findByWxOrderSn($wxOrderSn)
    {
        try {
            return $this->query()->where(['wx_order_sn' => $wxOrderSn])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            \Log::error('未查询到该订单, wx_order_sn: '.$wxOrderSn);
            throw new \ApiCustomException("未查询到该订单", 404);
        }
    }
    //是否已支付
    public function isPaied($id, $openid, $isThrow = true)
    {
        $order  = $this->getRowById($id, $openid);
        $status = $order->status;
        $return = true;

        switch ($status) {
            case 2:
            case 3:
            case 4:
                if ($isThrow) {
                    \Log::info('订单已支付, order_id: '.$id);
                    throw new \ApiCustomException("订单已支付", 410);
                }
                $return = true;
                break;
            case -1:
                if ($isThrow) {
                    \Log::info('订单已失效, order_id: '.$id);
                    throw new \ApiCustomException("订单已失效", 410);
                }
                $return = false;
                break;
            case 1:
                $return = $order;
                break;

            default:
                if ($isThrow) {
                    \Log::info('订单未知状态, order_id: '.$id.' 订单状态:'.$status);
                    throw new \ApiCustomException("订单未知状态", 410);
                }
                $return = false;
                break;
        }

        return $return;

    }

    //订单失效
    public function doLose($orderId)
    {
        $order = $this->query()->where('id', $orderId)->first();

        if ($order->status == 1) {
            $order->status = -1 ;
            $order->save();
        }
        return true;
    }

    //保存订单信息
    public function store($openid, $data)
    {
        $data['open_id']     = $openid;
        $data['order_sn']    = $this->createOrderSn();
        $data['wx_order_sn'] = 'wx_'.$data['order_sn'];

        return $this->query()->create($data);
    }
    //生成订单号
    public function createOrderSn()
    {
        return 'BT'.app('carbon')->now()->format('YmdHis').rand(100000, 999999);
    }
    //保存订单关联的商品
    public function storeOrderServers($orderId, $data)
    {
        $data['order_id']   = $orderId;

        return OrderServer::create($data);
    }

    public function storeOrderServerInfo($orderId, $rid)
    {
        $row = OrderServer::query()->where('order_id', $orderId)->first();

        $count     = $row->count;
        $serverId  = $row->server_id;
        $id        = $row->id;
        //保存服务的二维码
        for ($i=0; $i < $count; $i++) {
            OrderServerInfo::create([
                'order_servers_id' => $id,
                'server_id'        => $serverId,
                'rid'              => $rid,
                'code'             => md5(microtime(true).rand(1000, 9999).$id)
            ]);
        }

        return $serverId;
    }

    //获取订单关联的服务信息
    public function getOrderServers($orderId)
    {
        $order  = $this->findOrFailById($orderId);

        return $order->servers()->get();
    }

    //检查二维码
    public function checkOrderServerInfoByCode($rid, $code, $isThrow = true)
    {
        $row = OrderServerInfo::query()->where([
            'code' => $code,
            'rid'  => $rid
        ])->first();

        if (empty($row)) {
            if ($isThrow) {
                throw new \ApiCustomException("无效二维码", 410);
            }

            return ['status' => false, 'msg' => '无效二维码'];
        }

        if (!empty($row->used_at)) {
            if ($isThrow) {
                throw new \ApiCustomException("二维码已使用", 410);
            }

            return ['status' => false, 'msg' => '二维码已使用'];
        }

        $server = OrderServer::query()->where('id', $row->order_servers_id)->first();

        $order = $this->query()->find($server->order_id);

        return ['status' => true, 'orderServerId' => $row->order_servers_id, 'uOpenid' => $order->open_id];
    }

    public function getInfoByOrderServerId($id, $openid)
    {
        $server = OrderServer::query()->where('id', $id)->first();

        $order = $this->findOrFailById($server->order_id, $openid);

        $user = app(MemberRepository::class)->findByOpenid($openid);

        return ['server' => $server, 'order' => $order, 'user' => $user];
    }

    //核销兑换券
    public function finishCode($code, $orderServerId)
    {
        OrderServerInfo::where('code', $code)->update(['used_at' => app('carbon')->now()]);

        OrderServer::query()->where('id', $orderServerId)->increment('used_count');

        return true;
    }


}