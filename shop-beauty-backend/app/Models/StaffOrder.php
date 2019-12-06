<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffOrder extends Model
{

    protected $table = 'staff_order';

    protected $visible = ['id','staff', 'start_at', 'end_at'];

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'key');
    }

}
