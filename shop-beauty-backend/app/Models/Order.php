<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table   = 'orders';
    protected $visible = ['id', 'servers', 'price', 'status', 'paid_at','order_sn','settlement_amount',
        'pay_amount', 'extro_info', 'type', 'created_at', 'remark'];

    protected $guarded = [];

    public function toArray()
    {
        $array = parent::toArray();
        $array['type_s'] = app('enums')->orderType()->withCode($array['type'])->getName();
        $array['status_s'] = app('enums')->orderStatus()->withCode($array['status'])->getName();
        if ($array['status'] != -9) { //如果不是退款，进一步确定订单状态
            $row = OrderServer::where('order_id', $array['id'])->first();
            if ($row->count == $row->used_count) {
                $array['status_s'] = '已使用';
            } else {
                $array['status_s'] = '待使用';
            }
        }

        return $array;
    }

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }

    public function getSettlementAmountAttribute($value)
    {
        return $value / 100;
    }

    public function getPayAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function setSettlementAmountAttribute($value)
    {
        $this->attributes['settlement_amount'] = $value * 100;
    }

    public function setPayAmountAttribute($value)
    {
        $this->attributes['pay_amount'] = $value * 100;
    }

    public function servers()
    {
        return $this->hasOne(OrderServer::class, 'order_id', 'id');
    }

    public function getExtroInfoAttribute($value)
    {
        if (!empty($value)) {
            $ret = json_decode($value);

            $ret->payMethod_s = app('enums')->payMethod()->withCode($ret->payMethod)->getName();

            return $ret;
        }

        return $value;
    }

}
