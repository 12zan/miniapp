<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NaviBarGood extends Model
{
    protected $table = 'navi_bar_goods';

    protected $visible = ['good', 'goods_id'];

    public function good()
    {
        return $this->belongsTo(Good::class, 'goods_id', 'id');
    }
}
