<?php

namespace App\Repositories;

use App\Models\MemberMoneyLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MemberMoneyLogRepository extends BaseRepository
{
    const MODEL = MemberMoneyLog::class;

    public function findByIds($ids = [], $sort = [])
    {
        $query = $this->query()
            ->whereIn('id', $ids);

        foreach ($sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get();
    }

    public function findById($id)
    {
        $row = $this->findOrFailByid($id);

        return $row;
    }


    public function findOrFailByid($id)
    {
        try {
            return $this->query()->where('id', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            \Log::error('未查询到信息, id: '.$id);
            throw new \ApiCustomException("未查询到信息", 404);
        }
    }

    public function store($openid, $data)
    {
        $data['openid']     = $openid;

        return $this->query()->create($data);
    }
}