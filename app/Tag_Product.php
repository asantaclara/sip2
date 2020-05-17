<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag_Product extends Model
{
    protected $table = 'tag_products';
    protected $fillable = ['tag_id','product_id'];

}
