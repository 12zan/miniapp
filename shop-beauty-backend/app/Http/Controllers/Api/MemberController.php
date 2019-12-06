<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\MemberRepository;
use App\Repositories\MemberMoneyLogRepository;
use App\Repositories\QrCodeRepository;
use App\Util\QrCode;

class MemberController extends Controller
{

    public  $limit = 10; //每页条数
    private $member;
    private $log;
    private $qrCode;

    public function __construct(
        MemberRepository $member,
        MemberMoneyLogRepository $log,
        QrCodeRepository $qrCode
    ){
        $this->member = $member;
        $this->log    = $log;
        $this->qrCode = $qrCode;
    }

    //会员信息
    public function show()
    {
        $openid = app('sauth')->getAuthUserOpenId();

        $data = $this->member->findByOpenid($openid);

        return $this->responseJson($data);
    }

    //余额明细
    public function moneyLogsIndex()
    {
        $request = request();

        $ids = $this->getLogIds($request);

        $data = $this->log->findByIds($ids['ids'], $this->getSort());

        $pageInfo = $this->getPageInfo($ids['count']);

        return $this->responseJsonWithPage($data, $pageInfo);
    }
    //明细详情
    public function moneyLogsShow($id)
    {
        $row = $this->log->findById($id);

        return $this->responseJson($row);
    }

    //付款码
    public function payQrCode()
    {
        $request = request();

        $code = $request->input('code', null);
        $openid = app('sauth')->getAuthUserOpenId();
        //如果存在code，则为刷新
        if ($code) {
            $this->qrCode->delPayCode($code);
        }

        $code = $this->qrCode->createPayCode($openid);

        $type = app('enums')->qrCodeType()->withType('PAY')->getCode();
        $sCode = "type=$type&code=".$code;

        $image = QrCode::getBaseCode($sCode);

        return $this->responseJson(['image' => $image, 'code' => $code]);
    }

    protected function getLogIds()
    {
        $offset = $this->getOffset();
        $limit  = $this->getLimit();
        $sort   = $this->getSort();
        $openid = app('sauth')->getAuthUserOpenId();

        $query = $this->log->query();

        $query->where(['openid' => $openid]);

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return ['ids' => $query->pluck('id')->all(), 'count' => $count];
    }

}