<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Repositories\ShopSetRepository;
use App\Repositories\QrCodeRepository;
use App\Repositories\ServerItemRepository;
use App\Repositories\MemberRepository;
use App\Repositories\StaffRepository;

class HomeController extends Controller
{

    private $shop;
    private $serverItem;
    private $member;
    private $staff;

    public function __construct(
        ShopSetRepository $shop,
        ServerItemRepository $serverItem,
        MemberRepository $member,
        StaffRepository  $staff
    ){
        $this->shop       = $shop;
        $this->serverItem = $serverItem;
        $this->member     = $member;
        $this->staff      = $staff;
    }

    //首页
    public function show()
    {
        $rid = app('sauth')->getRidWhenUnauth();

        $data = [
            'shopInfo'  => $this->shop->getShopInfos($rid),
            'banner'    => $this->shop->getBanners($rid),
            'priceList' => $this->shop->getPriceList($rid),
            'staffs'    => $this->getStaffs(),
            'services'  => $this->getServices()
        ];

        return $this->responseJson($data);
    }

    public function preload()
    {
        $rid    = app('sauth')->getRid();
        $openid = app('sauth')->getAuthUserOpenId();

        $member  = $this->member->findByOpenid($openid);
        $isStaff = $this->staff->isStaff($openid);
        $orderStaff = $this->shop->getStaffOrderInfo($rid);
        $shopInfo = $this->shop->getShopInfos($rid);

        $data = [
            'member'           => $member,
            'isStaff'          => $isStaff,
            'shopInfo' => [
                'orderStaffTime'     => $orderStaff['openTime'],
                'isOpenStaffOrder'   => $orderStaff['status'],
                'memberMustUseWxPay' => $this->shop->memberMustUseWxPay($rid, false),
                'rechargeMin'        => $this->shop->getMinRecharge($rid),
                'logo'               => $shopInfo['logo']
            ]
        ];

        return $this->responseJson($data);
    }

    //验证二维码信息
    public function qrInfo()
    {
        $request = request();

        $this->validate($request, [
            'number' => 'required'
        ]);

        $rid = app('sauth')->getRidWhenUnauth();
        $number = $request->number;

       $data = app(QrCodeRepository::class)->getQrInfo($rid, $number);

       return $this->responseJson($data);
    }

    //搜集form_id
    public function saveFormId()
    {
        $request = request();

        $this->validate($request, [
            'formId' => 'required|string'
        ]);

        //避开调试器
        if ($request->formId == 'the formId is a mock one') {
            return $this->responseJson();
        }

        $openid = app('sauth')->getAuthUserOpenId();

        //如果存在，忽略
        if (\DB::table('form_ids')->where([
            'openid'     => $openid,
            'form_id'    => $request->input('formId')])->first()
         ) {
             return $this->responseJson('ok');
         }

        \DB::table('form_ids')->insert([
            'openid'     => $openid,
            'form_id'    => $request->input('formId'),
            'rid'        => app('sauth')->getRid(),
            'created_at' => app('carbon')->now()
        ]);

        return $this->responseJson();
    }

    private function getStaffs()
    {
        $offset = 0;
        $limit  = 4;
        $sort   = ['sort' => 'asc'];
        $rid    = app('sauth')->getRid();

        $query = $this->staff->query();

        //取出对应店铺的
        $query->where([
            'rid'     => $rid,
            'status'  => 1,
            'role_id' => 1
        ]);

        $count = $query->count();
        // 发型师数量不会太大
//        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        $ids = $query->pluck('id')->all();

        return $this->staff->findByIds($ids, $sort);

    }

    private function getServices()
    {
        $offset = 0;
        $limit  = 30;
        $sort   = ['sort' => 'asc'];
        $rid    = app('sauth')->getRid();

        $query = $this->serverItem->query();

        $query->where([
            'rid'    => $rid,
            'status' => 1
        ]);

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        $ids = $query->pluck('id')->all();

       return $this->serverItem->findByIds($ids, $sort);
    }

}