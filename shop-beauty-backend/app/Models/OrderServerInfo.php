<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Util\QrCode;

class OrderServerInfo extends Model
{
    protected $table   = 'order_servers_info';
    protected $visible = ['code', 'used_at', 'order_servers_id'];
    protected $guarded = [];

    public $timestamps = false;

    public function toArray()
    {
        $array = parent::toArray();

        $type = app('enums')->qrCodeType()->withType('EXCHANGE')->getCode();
        $value = 'type='.$type.'&code='.$array['code'];
        $image = QrCode::getBaseCode($value);

        $array['code_s'] = $image;

        $array['status'] = 1; //待使用

        if (!empty($array['used_at'])) {
            $array['status'] = -1;//'已使用'
        } else {
            $orderServer = OrderServer::find($array['order_servers_id']);
            $order       = Order::find($orderServer->order_id);

            if (in_array($order->status, [-9, -1, -2])) {
                $array['status'] = -2; //'已失效'
            }
        }

        return $array;
    }

}
