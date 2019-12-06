<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdImage extends Model
{
    protected $table = 'ad_image';

    protected $visible = ['url', 'images_id'];
}
