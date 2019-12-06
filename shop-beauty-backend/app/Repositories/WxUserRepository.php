<?php

namespace App\Repositories;

use App\Models\WxUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WxUserRepository extends BaseRepository
{
    const MODEL = WxUser::class;

    public function findOrFailByOpenid($openid, $isThrow = true)
    {
        try {
            return $this->query()->where('open_id', $openid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            if ($isThrow) {
                \Log::error('未查询到该用户, openid: '.$openid);
                throw new \ApiCustomException("未查询到该用户", 404);
            }
        }

        return false;
    }

    public function hasThisOpenid($openid)
    {
        $has = $this->findOrFailByOpenid($openid, false);

        if ($has) {
            return true;
        }
            return false;
    }

    public function store($data)
    {
        $data['created_at'] = app('carbon')->now();

        try {
            WxUser::firstOrCreate(['open_id' => $data['open_id']],$data);
        } catch(\Illuminate\Database\QueryException $e) {
            return true;
        }
    }

    public function update($openid, $data)
    {
        return $this->query()->where('open_id', $openid)->update($data);
    }
}