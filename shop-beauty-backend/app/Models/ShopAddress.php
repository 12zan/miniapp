<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopAddress extends Model
{
    protected $table = 'shop_address';

    protected $hidden = ['updated_at', 'created_at'];
}
