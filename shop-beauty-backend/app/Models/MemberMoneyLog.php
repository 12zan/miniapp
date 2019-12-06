<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberMoneyLog extends Model
{
    protected $table = 'member_money_logs';

    protected $guarded = [];

    const UPDATED_AT = NULL;

    public function toArray()
    {
        $array = parent::toArray();

        $array['code_s'] = app('enums')->moneyLogType()->withCode($array['code'])->getName();

        return $array;
    }

    public function getRemarkAttribute($value)
    {
        if (empty($value)) {
            return '无';
        }

        return $value;
    }

    public function getMoneyAttribute($value)
    {
        return round($value / 100, 2);
    }

    public function getDistMoneyAttribute($value)
    {
        return round($value / 100, 2);
    }

    public function setMoneyAttribute($value)
    {
        $this->attributes['money'] = round($value * 100);
    }

    public function setDistMoneyAttribute($value)
    {
        $this->attributes['dist_money'] = round($value * 100);
    }

}
