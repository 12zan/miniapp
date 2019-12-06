<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServerItem extends Model
{
    use SoftDeletes;

    protected $table = 'beauty_server';

    protected $visible = ['id', 'gno', 'name', 'price', 'member_price',
         'cover', 'banners', 'introduce', 'description', 'details', 'info', 'status'];

    public function toArray()
    {
        $array = parent::toArray();

        $array['name_s16'] = app('msalt')->subStr($array['name'], 16);
        $array['description_s30'] = app('msalt')->subStr($array['description'], 30);
        $array['description_s20'] = app('msalt')->subStr($array['description'], 20);

        return $array;
    }

    public function getPriceAttribute($value)
    {
        return $value / 100;
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

    public function getMemberPriceAttribute($value)
    {
        return $value / 100;
    }
    //封面图
    public function cover()
    {
        return $this->hasOne(ServerImage::class, 'beauty_server_id', 'id')->where('type', 2);
    }
    //轮播图
    public function banners()
    {
        return $this->hasManyThrough(Image::class, ServerImage::class, 'beauty_server_id', 'id', 'id', 'images_id')
            ->where('beauty_server_images.type', 1);
    }
    //介绍图
    public function introduce()
    {
        return $this->hasManyThrough(Image::class, ServerImage::class, 'beauty_server_id', 'id', 'id', 'images_id')
            ->where('beauty_server_images.type', 3);
    }

}
