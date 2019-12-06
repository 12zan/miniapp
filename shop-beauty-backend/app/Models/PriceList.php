<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    protected $table = 'price_list';

    protected $visible = ['name', 'price'];


    public function toArray()
    {
        $array = parent::toArray();

        $array['name_s7'] = app('msalt')->subStr($array['name'], 7);

        return $array;
    }

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }
}
