<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['post_id','user_id','dateTime','bookingPrice','quantity','giftCard'];

    public function post()
    {
        return $this->hasOne(Post::class, 'id', 'post_id');
    }


}
