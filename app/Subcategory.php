<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = ['title','category_id'];

    public function tags()
    {
        return $this->belongsToMany(
            'App\Tag',
            'subcategory_tags',
            'subcategory_id',
            'tag_id'
        );
    }
}
