<?php

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MemberRepository extends BaseRepository
{
    const MODEL = Member::class;

    public function findOrFailByOpenid($openid, $isThrow = true)
    {
        try {
            return $this->query()->where('openid', $openid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            if ($isThrow) {
                \Log::error('未查询到该会员信息, openid: '.$openid);
                throw new \ApiCustomException("未查询到该会员信息", 404);
            }
        }

        return false;
    }

    public function getMemberLavel($openid)
    {
        $row = $this->findOrFailByOpenid($openid, false);

        if (!$row) {
            return 0;
        }
        return $row->level;
    }

    //设置会员级别
    public function setMemberLevel($openid, $level)
    {
        $row = $this->findOrFailByOpenid($openid);

        if ($row->level == 0) {
            $row->fill(['level' => $level, 'member_at' => app('carbon')->now()]);
        }else{
            $row->fill(['level' => $level]);
        }

        $row->save();

        return true;
    }

    public function findByOpenid($openid)
    {
        $row = $this->findOrFailByOpenid($openid);

        return $row->where('openid', $openid)->with('wxUser')->first();
    }

    //余额是否充足
    public function hasMoney($openid, $money, $isThrow = true)
    {
        if ($money < 0) {
            throw new \ApiCustomException("金额不能为负数", 410);
        }
        $row = $this->findOrFailByOpenid($openid);

        if ($row->money < $money) {
            if ($isThrow) {
                throw new \ApiCustomException("余额不足", 405);
            }
            return false;
        }

        return $row;
    }

    //扣钱
    public function descMoney($openid, $money)
    {
        $row = $this->hasMoney($openid, $money);

        $row->money = $row->money - $money;
        $row->save();

        return $row->money;
    }
    //入账
    public function incrMoney($openid, $money, $type = 'wx')
    {
        if ($money < 0) {
            throw new \ApiCustomException("金额不能为负数", 410);
        }

        $row = $this->findOrFailByOpenid($openid);
        $row->money = $row->money + $money;
        $row->real_total_money = $row->real_total_money + $money;

        if ($type == 'wx')
        {
            $row->pay_total_money = $row->pay_total_money + $money;
        }

        $row->save();

        return $row->money;
    }

    // 获取会员授权手机号
    public function getPhone($openid)
    {
        $phone = '';
        $row = $this->query()->where('openid', $openid)->select('id', 'openid', 'phone')->first();

        if ($row)
        {
            $phone = $row->phone;
        }

        return $phone;
    }

    public function store($openid, $data)
    {
        $data['created_at'] = app('carbon')->now();
        $data['number']     = $this->makeNum($data['rid']);
        $data['openid']     = $openid;

        $row = $this->findOrFailByOpenid($openid, false);

        if ($row) {
            return true;
        }

        return $this->query()->insert($data);
    }

    public function update($openid, $data)
    {
        $row = $this->findOrFailByOpenid($openid);

        $this->query()->where('openid', $openid)->update($data);

        return true;
    }

    //生成会员号
    private function makeNum($rid)
    {
        $row = $this->query()->where('rid', $rid)->orderBy('id', 'desc')->first();

        if ($row) {
            return $row->number + 1;
        }

        return 100;
    }
}