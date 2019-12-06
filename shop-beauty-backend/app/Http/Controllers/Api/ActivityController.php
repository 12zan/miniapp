<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ActivityRepository;

class ActivityController extends Controller
{

    private $item;
    public  $limit = 10;

    public function __construct(ActivityRepository $item)
    {
        $this->item = $item;
    }

    public function index()
    {
        // 同时只有一个活动生效
        $id = $this->getItemIds();

        // 修改查找方式，因为进行中的活动只要一个，所以筛选条件变为关联层级
        $condition = request()->input('condition', null);

        $data = $this->item->findByCondition($id, $condition);

        return $this->responseJson($data);
    }

    protected function getItemIds()
    {
        $offset = $this->getOffset();
        $limit  = $this->getLimit();
        $sort   = $this->getSort();
        $rid    = app('sauth')->getRid();

        $query = $this->item->query();

        $query->where([
            'rid'    => $rid,
            'status' => 1,
            'type'   => '0301'
        ]);

        //时间区间
        $query->where('time_start', '<', app('carbon')->now()->format('Y-m-d H:i:s'))
            ->where('time_end', '>', app('carbon')->now()->format('Y-m-d H:i:s'));

        // 同时只会有一个活动存在，短期大于长期
        $query->orderBy('time_type', 'desc');

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->first()->id;
    }

}