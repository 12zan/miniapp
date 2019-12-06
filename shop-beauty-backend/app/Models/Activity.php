<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity';

    protected $visible = ['name', 'rid', 'recharge'];

    //关联充值条件
    public function recharge()
    {
        return $this->hasMany(RechargeActivity::class, 'activity_id', 'id');
    }
}
