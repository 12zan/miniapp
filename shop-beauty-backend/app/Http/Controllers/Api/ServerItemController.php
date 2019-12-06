<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ServerItemRepository;

class ServerItemController extends Controller
{
    private $item;

    public function __construct(ServerItemRepository $item)
    {
        $this->item = $item;
    }

    public function index()
    {
        $request = request();

        $ids = $this->getItemIds($request);

        $data = $this->item->findByIds($ids['ids'], $this->getSort());

        $pageInfo = $this->getPageInfo($ids['count']);

        return $this->responseJsonWithPage($data, $pageInfo);
    }

    public function show($id)
    {
        $rid = app('sauth')->getRid();

        $show = $this->item->findById($id, $rid);

        return $this->responseJson($show);
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
            'status' => 1
        ]);

        $count = $query->count();
        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy($field, $order);
        }

        return ['ids' => $query->pluck('id')->all(), 'count' => $count];
    }
}