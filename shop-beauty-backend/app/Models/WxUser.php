<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WxUser extends Model
{
    protected $table = 'wx_users';

    protected $visible = ['id', 'open_id', 'wxStaff', 'avatar', 'rid', 'nickname', 'gender'];

    protected $guarded = [];

    public function toArray()
    {
        $array = parent::toArray();

        $array['nickname_s7'] = app('msalt')->substr($array['nickname'], 7);

        return $array;
    }

    public function wxStaff()
    {
        return $this->hasOne(WxUserStaff::class, 'open_id', 'open_id');
    }
}
