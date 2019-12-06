<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechargeActivityGift extends Model
{
    protected $table = 'recharge_activity_gift';

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }

    //赠送的服务
    public function services()
    {
        return $this->hasOne(ServerItem::class, 'id', 'relation_id');
    }
}
