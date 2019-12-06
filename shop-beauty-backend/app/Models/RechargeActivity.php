<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechargeActivity extends Model
{
    protected $table = 'recharge_activity';


    public function getConditionAttribute($value)
    {
        return $value / 100;
    }

    //关联的礼物
    public function gifts()
    {
        return $this->hasMany(RechargeActivityGift::class, 'recharge_activity_id', 'id');
    }
}
