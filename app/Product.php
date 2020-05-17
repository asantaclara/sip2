<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['title','description','price'];

    public function photos()
    {
        return $this->hasMany(Product_Image::class, 'product_id');
    }

    public function tags() {
        return $this->belongsToMany(
            'App\Tag',
            'tag_products',
            'tag_id',
            'product_id'
        );
    }
}
