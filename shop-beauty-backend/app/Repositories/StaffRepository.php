<?php

namespace App\Repositories;

use App\Models\Staff;
use App\Models\WxUserStaff;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StaffRepository extends BaseRepository
{
    const MODEL = Staff::class;

    public function findByIds($ids = [], $sort = [])
    {
        $query = $this->query()
            ->with('image')
            ->whereIn('id', $ids);

        foreach ($sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get();
    }

    //是否有扫码扣款的权限 FIXME
    public function hasAuthToScan($openid, $rid, $isThrow = true)
    {
        // $wxUser = app(WxUserRepository::class)->findOrFailByOpenid($openid);
        $staff = WxUserStaff::query()->where([
            'open_id' => $openid,
            'scan'    => 1
        ])->first();

        if (!empty($staff)) {
            return ['status' => true, 'staffId' => $staff->staff_id];
        }

        if ($isThrow) {
            throw new \ApiCustomException("无扫码权限", 410);
        }
        return ['status' =>false, 'msg' => '无扫码权限'];
    }

    //是否是员工
    public function isStaff($openid)
    {
        $staff = WxUserStaff::where(['open_id' => $openid])->first();

        if (!empty($staff)) {
            return true;
        }
            return false;
    }

    //扫码添加员工
    public function storeStaff($openid, $rid)
    {
        if ($this->isStaff($openid)) {
            $staff = WxUserStaff::where(['open_id' => $openid])->first();

            $staff->fill(['updated_at' => app('carbon')->now()]);

            $staff->save();

            return true;
        }

        WxUserStaff::create([
            'open_id' => $openid,
            'rid'     => $rid
        ]);

        return true;
    }


    public function findById($id, $rid)
    {
        $row = $this->findOrFailByid($id, $rid);

        $with = $row->where('id', $id)
            ->with('image');

        return $with->first();
    }

    public function findOrFailByid($id, $rid, $isThrow = true)
    {
        try {
            return $this->query()->where([
                'id'     => $id,
                'rid'    => $rid,
                'status' => 1
            ])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            if ($isThrow) {
                \Log::error('未查询到该理发师, id: '.$id);
                throw new \ApiCustomException("未查询到该理发师", 404);
            }
            return null;
        }
    }
}