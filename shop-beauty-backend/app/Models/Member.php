<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';

    protected $visible = ['id', 'openid', 'money', 'wxUser', 'number', 'phone', 'level'];

    protected $guarded = [];

    public function wxUser()
    {
        return $this->belongsTo(WxUser::class, 'openid', 'open_id');
    }

    public function getMoneyAttribute($value)
    {
        return round($value / 100, 2);
    }

    public function getRealTotalMoneyAttribute($value)
    {
        return round($value / 100, 2);
    }

    public function getPayTotalMoneyAttribute($value)
    {
        return round($value / 100, 2);
    }

    public function setMoneyAttribute($value)
    {
        $this->attributes['money'] = round($value * 100);
    }

    public function setRealTotalMoneyAttribute($value)
    {
        $this->attributes['real_total_money'] = round($value * 100);
    }

    public function setPayTotalMoneyAttribute($value)
    {
        $this->attributes['pay_total_money'] = round($value * 100);
    }
}
