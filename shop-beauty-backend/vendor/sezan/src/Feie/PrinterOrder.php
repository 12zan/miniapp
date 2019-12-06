<?php

namespace SeZan\Feie;

use SeZan\Kernel\Http\HttpClient;
use SeZan\Kernel\Exceptions\CustomeException;

/**
 * 打印订单
 */

class PrinterOrder extends Feie
{
    protected $privateParams = [];

    /**
     *       sn  必须  string  打印机编号
      *      content 必须  string  打印内容,不能超过5000字节
     *       times 打印次数
     */
    public function setParames($data)
    {
        $this->privateParams['sn']      = $data['sn'];
        $this->privateParams['content'] = $this->handleOrderInfo($data['orderInfo']);
        $this->privateParams['times']   = !empty($data['times']) ? $data['times'] : 1;

        return $this;
    }

    protected function handleOrderInfo($orderInfo = [])
    {
        $order_type = $orderInfo['type'] == 1 ? '外送' : '到店';

        $str = '<B><C><BOLD>'.$orderInfo['title'].'</BOLD></C></B><BR>';
        $str .= '<C><BOLD>--------已在线支付--------</BOLD></C><BR>';
        $str .= '下单时间：'.date('Y-m-d H:i:s').'<BR>';
        $str .= '订单编号：'.$orderInfo['order_sn'].'<BR>';
        $str .= '订单类型：'.$order_type.'<BR>';
        $str .= '--------------------------------<BR>';
        $str .= '商品名称　　　　　　单价　　数量<BR>';

        // 一行总共32字符，中文2字符，数量从第21到26 单价一般是29到32，过长可以往前移
        // 商品名过长，则两行显示，数量和单价在第一行显示
        // name 最长20 price最长6 numner最长4
        foreach ($orderInfo['goods'] as $key => $value) {
            $value['name'] = $this->supStr($value['name'], 20);
            $value['price'] = $this->supStr($value['price'], 7);
            $value['number'] = $this->supStr($value['number'], 3);

            $new_str = $this->getNewStr($value['name'], '', $value['price'], $value['number']);

            $str .= $new_str;
        }

        $str .= '--------------------------------<BR>';
        //        // 折扣 暂时没有
        //        if (!empty($orderInfo['discounts'])) {
        //            foreach ($orderInfo['discounts'] as $value) {
        //                $str .= '<B>'.$value.'</B>'.'<BR>';
        //            }
        //        }

        if ($orderInfo['type']== 1 and !empty($orderInfo['freight']))
        {
            $str .= '配送费：'.$orderInfo['freight'].'<BR>';
            $str .= '--------------------------------<BR>';
        }

        $str .= '已付：<RIGHT><B>'.$orderInfo['count'].'</B></RIGHT><BR>';
        $str .= '--------------------------------<BR>';

        if (!empty($orderInfo['remark']))
        {
            $str .= '备注：<BOLD>'.$orderInfo['remark'].'</BOLD><BR>';
            $str .= '--------------------------------<BR>';
        }

        switch ($orderInfo['type'])
        {
            case 1:// 外送
                $str .= $orderInfo['address'].'<BR>';
                $str .= '<B><BOLD>'.$orderInfo['address_detail'].'</BOLD><BR>';
                $str .= '<B><BOLD>'.$orderInfo['buyer'].'</BOLD></B><BR>';
                $str .= '<B><BOLD>'.$orderInfo['phone'].'</BOLD></B><BR>';
                break;

            case 2:// 取餐号
                $str .= '取餐号：<BR>';
                $str .= '<B><C><BOLD>'.$orderInfo['number_a'].'</BOLD></C></B><BR>';
                break;

            case 3:// 带桌号
                $str .= '桌号：<BR>';
                $str .= '<B><C><BOLD>'.$orderInfo['number_b'].'</BOLD></C></B><BR>';
                break;
        }

        return $str;
    }

    public function getApiName()
    {
        return 'Open_printMsg';
    }

    protected function sstrlen($str) {
        $n = (strlen($str) + mb_strlen($str,"UTF8")) / 2;
        return $n;
    }

    protected function supStr($str, $total_len)
    {
        $space = '                    ';
        $len = $this->sstrlen($str);

        if ($len < $total_len)
        {
            $str .= substr($space, 0, $total_len - $len);
        }

        return $str;
    }

    protected function getNewStr($name, $str = '', $price = '', $number = '')
    {
        $name_len = $this->sstrlen($name);

        if ($name_len > 20)
        {
            $new_name = mb_substr($name, 0, 10);
            $new_name = $this->supStr($new_name, 20);
            $left_name = mb_substr($name, 10);

            $str .= $new_name.' '.$price.' '.$number.'<BR>';

            $str = $this->getNewStr($left_name, $str);
        }
        else
        {
            $str .= $name.' '.$price.' '.$number.'<BR>';
        }

        return $str;
    }


}

?>