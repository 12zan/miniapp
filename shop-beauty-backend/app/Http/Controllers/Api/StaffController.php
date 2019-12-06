<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\StaffRepository;
use App\Repositories\QrCodeRepository;
use App\Repositories\WxUserRepository;
use App\Repositories\MemberRepository;
use App\Repositories\OrderRepository;
use App\Repositories\MemberMoneyLogRepository;
use App\Util\WebSocket;

class StaffController extends Controller
{

    private   $item;
    private   $member;
    protected $sort = ['sort' => 'asc'];
    public    $limit = 3;

    public function __construct(
        StaffRepository $item,
        MemberRepository $member
    )
    {
        $this->item   = $item;
        $this->member = $member;
    }

    public function index()
    {
        $request = request();

        $this->limit = 5;// 分页改为5

        $ids = $this->getItemIds($request);

        $data = $this->item->findByIds($ids['ids'], $this->getSort());

        $pageInfo = $this->getPageInfo($ids['count']);

        return $this->responseJsonWithPage($data, $pageInfo);
    }

    //添加员工
    public function store()
    {
        $request = request();
        $openid  = app('sauth')->getAuthUserOpenId();
        $rid     = app('sauth')->getRid();

        app(WxUserRepository::class)->findOrFailByOpenid($openid);

        $this->item->storeStaff($openid, $rid);

        return $this->responseJson('ok');
    }

    //发型师列表
    public function miniIndex()
    {
        $rid = app('sauth')->getRid();

        $data = $this->item->query()->where([
            'rid'     => $rid,
            'status'  => 1,
            'role_id' => 1
        ])->select('id', 'role', 'name')->get();

        return $this->responseJson($data);
    }

    public function show($id)
    {
        $rid = app('sauth')->getRid();

        $show = $this->item->findById($id, $rid);

        return $this->responseJson($show);
    }

    //扫码扣款
    public function scanQrcode()
    {
        $request = request();
        $this->validate($request, [
            'code' => 'required'
        ]);
        $code   = $request->code;
        $openid = app('sauth')->getAuthUserOpenId();
        $rid    = app('sauth')->getRid();
        // 是否有扫码权限
        $rret = $this->item->hasAuthToScan($openid, $rid, false);

        if (!$rret['status']) {
            return $this->responseErrorJson($rret['msg'], null, '1');
        }

        $ret = app(QrCodeRepository::class)->payCodeIsAvaiable($code, false);

        if (!$ret['status']) {
            return $this->responseErrorJson($ret['msg'], null, '2');
        }
        $mOpenid = $ret['code'];

        $row = $this->member->findByOpenid($mOpenid);

        $key = 'beaty_key_'.md5(microtime(true));
        \Cache::put($key, true, 10);

        return $this->responseJson(['userInfo' => $row, 'key' => $key]);
    }
    //确定扣款
    public function receiptByQrcode()
    {
        $request = request();
        $this->validate($request, [
            'code'    => 'required',
            'uOpenid' => 'required',
            'key'     => 'required',
            'money'   => 'required'
        ]);
        $code    = $request->code;
        $uOpenid = $request->uOpenid;
        $money   = $request->money;
        $key     = $request->key;
        $openid  = app('sauth')->getAuthUserOpenId();
        $rid     = app('sauth')->getRid();
        //检查页面Key是否有效
        $rret = $this->checkKey($key, false);

        if (!$rret['status']) {
            return $this->responseErrorJson($rret['msg'], null, '1');
        }
        // 是否有扫码权限
        $ret = $this->item->hasAuthToScan($openid, $rid, false);

        if (!$ret['status']) {
            return $this->responseErrorJson($ret['msg']);
        }
        $staffId = $ret['staffId'];
        //扣钱
        $distMoney = $this->member->descMoney($uOpenid, $money);
        //删除二维码
        app(QrCodeRepository::class)->delPayCode($code);
        //记录日志
        $data = [
            'rid'        => $rid,
            'code'       => app('enums')->moneyLogType()->withType('QR_DES')->getCode(),
            'money'      => $money ,
            'dist_money' => $distMoney,
            'type'       => 'out',
            'remark'     => '扫码扣款',
            'created_user' => $staffId
        ];
        $m = app(MemberMoneyLogRepository::class)->store($uOpenid, $data);
        //推送websocket
        WebSocket::pushData($code, ['status' => 'success', 'money' => $money, 'id' => $m->id]);

        return $this->responseJson('ok');
    }

    //扫核销码，预览页面
    public function preScanExchangeCode()
    {
        $request = request();

        $this->validate($request, [
            'code' => 'required'
        ]);
        $code   = $request->code;
        $openid = app('sauth')->getAuthUserOpenId();
        $rid    = app('sauth')->getRid();
        // 是否有扫码权限
        $retT = $this->item->hasAuthToScan($openid, $rid, false);

        if (!$retT['status']) {
            return $this->responseErrorJson($retT['msg'], null, '1');
        }

        $ret = app(OrderRepository::class)->checkOrderServerInfoByCode($rid, $code, false);

        if (!$ret['status']) {
            return $this->responseErrorJson($ret['msg']);
        }

        $row = app(OrderRepository::class)->getInfoByOrderServerId($ret['orderServerId'], $ret['uOpenid']);

        $key = 'beaty_excode_key_'.md5(microtime(true));
        \Cache::put($key, true, 10);

        return $this->responseJson([
            'userInfo'     => $row['user'],
            'key'          => $key,
            'serviceName'  => $row['server']['name'],
            'servicePrice' => $row['server']['server_price'],
            'payMethod_s'  => $row['order']['extro_info']->payMethod_s
        ]);
    }

    //扫核销码，确定核销
    public function surePreScanExchangeCode()
    {
        $request = request();

        $this->validate($request, [
            'code' => 'required',
            'key'  => 'required'
        ]);
        $code   = $request->code;
        $openid = app('sauth')->getAuthUserOpenId();
        $rid    = app('sauth')->getRid();
        $key    = $request->key;
        //检查页面Key是否有效
        $rret = $this->checkKey($key, false);
        if (!$rret['status']) {
            return $this->responseErrorJson($rret['msg'], null, '1');
        }
        //FIXME 是否有扫码权限
        $retT = $this->item->hasAuthToScan($openid, $rid, false);
        if (!$retT['status']) {
            return $this->responseErrorJson($retT['msg'], null, '1');
        }

        $ret = app(OrderRepository::class)->checkOrderServerInfoByCode($rid, $code, false);

        if (!$ret['status']) {
            return $this->responseErrorJson($ret['msg']);
        }

        app(OrderRepository::class)->finishCode($code, $ret['orderServerId']);
         //推送websocket
        // WebSocket::pushData($key, ['status' => 'success']);

        return $this->responseJson('ok');
    }

    private function checkKey($key, $isThrow = true)
    {
        if (empty(\Cache::pull($key))) {
            if ($isThrow) {
                throw new \ApiCustomException('页面已失效,请重新扫码', 410);
            }
            return ['status' => false, 'msg' => '页面已失效,请重新扫码'];
        }

        return ['status' => true];
    }

    public function getItemIds()
    {
        $offset = $this->getOffset();
        $limit  = $this->getLimit();
        $sort   = $this->getSort();
        $rid    = app('sauth')->getRid();

        $query = $this->item->query();

        //取出对应店铺的
        $query->where([
            'rid'     => $rid,
            'status'  => 1,
            'role_id' => 1
        ]);

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return ['ids' => $query->pluck('id')->all(), 'count' => $count];
    }

}