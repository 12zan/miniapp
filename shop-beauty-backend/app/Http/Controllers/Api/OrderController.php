<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Repositories\ServerItemRepository;
use App\Repositories\ShopSetRepository;
use App\Jobs\HandleTimeoutOrder;

class OrderController extends Controller
{

    private $order;
    private $good;
    public  $limit = 4; //每页条数

    public function __construct(
        OrderRepository      $order,
        ServerItemRepository $good
    ) {
        $this->order = $order;
        $this->good  = $good;
    }

    public function index()
    {
        $request = request();

        $ids = $this->getOrderIds($request);

        $data = $this->order->findByIds($ids['ids'], $this->getSort());

        $pageInfo = $this->getPageInfo($ids['count']);

        return $this->responseJsonWithPage($data, $pageInfo);
    }

    public function show($id)
    {
        $openid  = app('sauth')->getAuthUserOpenId();
        $data = $this->order->getRowById($id, $openid);

        return $this->responseJson($data);
    }

    public function find($id)
    {
        $openid = app('sauth')->getAuthUserOpenId();
        $data   = $this->order->getRowById($id, $openid);
        $status = $data->status;
        $i = 0;

        while(($status == 1) && $i < 4) {
            sleep(1);
            $data = $this->order->getRowById($id, $openid);
            $status = $data->status;
            $i++;
        }

        if ($status == 2) {
            $ret = (array) $data->extro_info;
            $ret['orderId'] = $id;
            return $this->responseJson($ret);
        }

        return $this->responseErrorJson('支付失败');
    }

    //购买服务下单
    public function store()
    {
        $request = request();
        $openid  = app('sauth')->getAuthUserOpenId();
        $appid   = app('sauth')->getAppId();
        $rid     = app('sauth')->getRid();

        $this->validate($request, [
            'serverId'  => 'required',
            'count'     => 'required',
            'phone'     => 'required|size:11',
            'payMethod' => 'required',
            'priceType' => 'required'
        ]);

        $serverId = $request->serverId;
        $count    = $request->count;
        $phone    = $request->phone;

        $goods = $this->good->findById($serverId, $rid);

        $price = $goods->price;
        $code  = app('enums')->orderType()->withType('BUY_SERVER')->getCode(); //美业购买服务订单
        $payMethod = $request->payMethod;
        $priceType = $request->priceType;
        //如果是会员
        if ($priceType == 1) {
            $price = $goods->member_price;
        }

        $data['appid']             = $appid;
        $data['price']             = $price * $count;
        $data['settlement_amount'] = $price * $count;
        $data['remark']            = $request->input('remark', '');
        $data['rid']               = $rid;
        $data['type']              = $code;
        $data['extro_info']        = json_encode([
                                        'phone'     => $phone,
                                        'payMethod' => $payMethod,
                                        'priceType' => $priceType
                                    ]);

        $orderId = 0;

        \DB::transaction(function () use ($data, $openid, &$orderId, $goods, $count, $price) {
            //保存订单
            $order = $this->order->store($openid, $data);

            $orderId = $order->id;
            //保存订单关联的商品
            $orderGoods = [
                'server_id'    => $goods->id,
                'count'        => $count,
                'price'        => $price,
                'name'         => $goods->name,
                'server_price' => $goods->price,
                'info'         => $goods->info ? json_encode($goods->info) : '',
                'details'      => $goods->details ? json_encode($goods->details) : '',
                'image'        => $goods->cover->image ? $goods->cover->image->path : ''
            ];
            $this->order->storeOrderServers($orderId, $orderGoods);

        }, 2);

        return $this->responseJson(['orderId' => $orderId]);
    }

    //储值订单
    public function storeValue()
    {
        $request = request();
        $openid  = app('sauth')->getAuthUserOpenId();
        $appid   = app('sauth')->getAppId();
        $rid     = app('sauth')->getRid();

        $this->validate($request, [
            'money' => 'required|numeric|min:0',
        ]);

        $money    = $request->money;

        //FIXME 判断充值金额是否满足最低金额
        // $goods = $this->good->findById($serverId, $rid);

        $code  = app('enums')->orderType()->withType('STORE')->getCode(); //美业购买服务订单
        $payMethod = app('enums')->payMethod()->withType('WXPAY')->getCode();

        $data['appid']             = $appid;
        $data['price']             = $money;
        $data['settlement_amount'] = $money;
        $data['remark']            = $request->input('remark', '');
        $data['rid']               = $rid;
        $data['type']              = $code;
        $data['extro_info']        = json_encode([
                                        'phone'     => '',
                                        'payMethod' => $payMethod,
                                        'priceType' => ''
                                    ]);

        $order = $this->order->store($openid, $data);

        return $this->responseJson(['orderId' => $order->id]);
    }

    protected function getOrderIds()
    {
        $offset = $this->getOffset();
        $limit  = $this->getLimit();
        $sort   = $this->getSort();
        $openid = app('sauth')->getAuthUserOpenId();

        $query = $this->order->query();

        $query->where(['open_id' => $openid])
              ->whereIn('type', ['0301', '0303'])
              ->whereIn('status', [2,3,4,5,-9]);

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return ['ids' => $query->pluck('id')->all(), 'count' => $count];
    }

}