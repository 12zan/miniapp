<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderServer extends Model
{
    protected $table = 'order_servers';

    protected $visible = ['image', 'infos', 'name', 'count', 'price', 'server_price', 'details', 'info', 'used_count', 'order_id'];

    protected $guarded = [];

    const UPDATED_AT = NULL;

    public function getServerPriceAttribute($value)
    {
        return $value / 100;
    }

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }

    public function infos()
    {
        return $this->hasMany(OrderServerInfo::class, 'order_servers_id', 'id');
    }

    public function getDetailsAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        return json_decode($value);

    }

    public function getInfoAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        return json_decode($value);
    }

    public function setServerPriceAttribute($value)
    {
        $this->attributes['server_price'] = $value * 100;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }


}
