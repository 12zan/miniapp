<?php

namespace App\Repositories;

use App\Models\StaffOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StaffOrderRepository extends BaseRepository
{
    const MODEL = StaffOrder::class;

    public function findByIds($ids = [], $sort = [])
    {
        $query = $this->query()
            ->with('staff.image')
            ->whereIn('id', $ids);

        foreach ($sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get();
    }

    public function findById($id, $openid)
    {
        $row = $this->findOrFaild($id, $openid);

        return $row->with('staff.image')->first();
    }

    public function findOrFaild($id, $openid, $isThrow = true)
    {
        try {
            return $this->query()
                ->where(['id' => $id, 'openid' => $openid])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            if ($isThrow) {
                \Log::error('未查询到该订单, order_id: '.$id);
                throw new \ApiCustomException("未查询到该订单", 404);
            }
            return null;
        }
    }

    //检查预约次数
    public function checkOrderTimes($openid, $isThrow = true)
    {
        $count = $this->query()->where('openid', $openid)->where('created_at', '>=', date('Y-m-d'.' 00:00:00'))->count();

        if ($count >= 3) {
            if ($isThrow) {
                throw new \ApiCustomException("一天只能预约三次", 410);
            }

            return false;
        }

        return true;
    }

    //保存订单信息
    public function store($openid, $data)
    {
        $data['created_at']  = app('carbon')->now();
        $data['openid']      = $openid;

        return $this->query()->insertGetId($data);
    }


}