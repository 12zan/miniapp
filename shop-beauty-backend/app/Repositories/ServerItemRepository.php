<?php

namespace App\Repositories;

use App\Models\ServerItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServerItemRepository extends BaseRepository
{
    const MODEL = ServerItem::class;

    public function findByIds($ids = [], $sort = [])
    {
        $query = $this->query()
                ->with('cover.image')
                ->whereIn('id', $ids);

        foreach ($sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get();
    }

    public function findById($id, $rid)
    {
        $good = $this->findOrFailByid($id, $rid);

        $with = $good->with('banners')
            ->with('introduce')
            ->with('cover.image')
            ->where('id', $id);

        return $with->first();
    }

    public function findOrFailByid($id, $rid)
    {
        try {
            return $this->query()->where([
                'id'     => $id,
                'rid'    => $rid
            ])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            \Log::error('未查询到该服务 id: '.$id);
            throw new \ApiCustomException("未查询到该服务", 404);
        }
    }
}