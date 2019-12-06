<?php

namespace App\Repositories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityRepository extends BaseRepository
{
    const MODEL = Activity::class;

    public function findByIds($ids = [], $sort = [])
    {
        $query = $this->query()
            ->with('recharge.gifts.services.cover.image')
            ->whereIn('id', $ids);

        foreach ($sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->get();
    }

    public function findByCondition($id, $condition = null)
    {
        $query = $this->query()
            ->with('recharge.gifts.services.cover.image')
            ->where('id', $id);

        if ($condition) {
            $condition = $condition * 100;
            $query->with(['recharge' => function ($q) use ($condition) {
                $q->where('condition', '<=', $condition);
            }]);
        }

        return $query->first();
    }


}