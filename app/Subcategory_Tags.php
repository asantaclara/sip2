<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subcategory_Tags extends Model
{
    protected $table = 'subcategory_tags';
    protected $fillable = ['subcategory_id','tag_id'];

}
