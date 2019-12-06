<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use ValidatesRequests;

    public    $limit = 8; //每页条数
    protected $sort  = ['id' => 'desc'];

    protected function getOffset()
    {
        return  ($this->getCurrentPage() - 1) * $this->getLimit();
    }

    // get limit
    protected function getLimit()
    {
        return $this->limit;
    }
    //get Current Page
    protected function getCurrentPage()
    {
        return request()->input('page', 1);
    }
    //get sort
    protected function getSort()
    {
        return $this->sort;
    }
    //
    protected function responseJsonWithPage($data, $pageInfo)
    {
        return response()->json([
                'data' => $data,
                'page' => $pageInfo
            ]);
    }
    //成功返回
    protected function responseJson($data = null)
    {
        return response()->json(['data' => $data, 'msg' => 'ok', 'status' => 'success']);
    }
    //失败返回
    protected function responseErrorJson($msg = null, $data = null, $errorNo = '0')
    {
        return response()->json(['data' => $data, 'msg' => $msg, 'status' => 'failed', 'errorno' => $errorNo]);
    }
    //
    protected function getPageInfo($count)
    {
        return [
                'current'   => (int) $this->getCurrentPage(),
                'totalPage' => ceil($count/$this->getLimit()),
                'total'     => $count
            ];
    }

}
