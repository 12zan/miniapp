<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerImage extends Model
{
    protected $table = 'beauty_server_images';

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'images_id');
    }

}
