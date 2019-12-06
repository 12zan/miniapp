<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\StaffOrderRepository;
use App\Repositories\StaffRepository;

class StaffOrderController extends Controller
{
    private $item;

    public function __construct(StaffOrderRepository $item)
    {
        $this->item = $item;
    }

    public function index()
    {
        $request = request();

        $ids = $this->getItemIds();

        $data = $this->item->findByIds($ids['ids'], $this->getSort());

        $pageInfo = $this->getPageInfo($ids['count']);

        return $this->responseJsonWithPage($data, $pageInfo);
    }

    public function show($id)
    {
        $openid = app('sauth')->getAuthUserOpenId();

        $show = $this->item->findById($id, $openid);

        return $this->responseJson($show);
    }

    //存储
    public function store()
    {
        $request = request();

        $this->validate($request, [
            'phone'    => 'required',
            'start_at' => 'required',
            'end_at'   => 'required',
            'key'      => 'required'
        ]);
        $openid  = app('sauth')->getAuthUserOpenId();
        $rid     = app('sauth')->getRid();

        app(StaffRepository::class)->findOrFailByid($request->key, $rid);

        //一天只能约三次判断
        if(!$this->item->checkOrderTimes($openid, false)){
            return $this->responseErrorJson('一天只能约三次');
        }

        $data = $request->only(['phone', 'start_at', 'end_at', 'key']);

        $data['rid']    = $rid;

        $id = $this->item->store($openid, $data);

        return $this->responseJson(['id' => $id]);
    }

    protected function getItemIds()
    {
        $offset = $this->getOffset();
        $limit  = $this->getLimit();
        $sort   = $this->getSort();
        $openid = app('sauth')->getAuthUserOpenId();

        $query = $this->item->query();

        $query->where([
            'openid' => $openid
        ]);

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return ['ids' => $query->pluck('id')->all(), 'count' => $count];
    }
}