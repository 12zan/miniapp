<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';

    protected $hidden = ['key', 'openid', 'updated_at', 'created_at'];
}
