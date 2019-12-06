<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $visible = ['id', 'name', 'intro', 'role', 'image'];


    public function toArray()
    {
        $array = parent::toArray();

        $array['name_s5'] = app('msalt')->subStr($array['name'], 5);
        $array['role_s7'] = app('msalt')->subStr($array['role'], 7);

        return $array;
    }

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image');
    }


}
