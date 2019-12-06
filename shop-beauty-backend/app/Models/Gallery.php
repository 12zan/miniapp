<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'gallery';

    protected $visible = ['name', 'rid', 'image'];

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'images_id');
    }
}
