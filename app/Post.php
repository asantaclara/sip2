<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Post extends Model
{
    protected $fillable = ['product_id','startDate','endDate','active'];

    public function product()
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }

    public function qtySuscriptors()
    {
        return Subscription::where('post_id', $this->id)->get()->sum('quantity');
    }

    public function actualDiscount()
    {
        $discount = Discount::where('post_id', $this->id)
            ->where('quantityStart', '<=', $this->qtySuscriptors())
            ->orderBy('quantityStart')
            ->get()->last();

        return $discount ? $discount->discount : 0;
    }

    public function nextDiscount()
    {
        $nextDiscount = Discount::where('post_id', $this->id)
            ->where('quantityStart', '>', $this->qtySuscriptors())
            ->orderBy('quantityStart')
            ->first();

        return $nextDiscount ? $nextDiscount->discount : 0;
    }

    public function qtyToNextDiscount()
    {
        $nextDiscount = Discount::where('post_id', $this->id)
            ->where('quantityStart', '>', $this->qtySuscriptors())
            ->orderBy('quantityStart')
            ->first();

        return $nextDiscount ? $nextDiscount->quantityStart -  $this->qtySuscriptors() : 0;
    }

    public function actualPrice()
    {
        return round((1-$this->actualDiscount()) * $this->product->price,2);
    }

    public function recommendations()
    {
        $tags = $this->product->tags->pluck('id');

        $productsId = Tag_Product::whereIn('tag_id', $tags)->select('product_id');

        $posts = Post::whereIn('product_id',$productsId)
            ->where('id','!=', $this->id)
            ->limit(3)
            ->get();

        $extraPosts = Post::whereNotIn('product_id',$productsId)
            ->where('id','!=', $this->id)
            ->limit(3-count($posts))
            ->get();

        $result = [];

        $posts = $posts->merge($extraPosts);

        foreach ($posts as $post) {
            $aux = [];
            $aux['id'] = $post->id;
            $aux['title'] = $post->product->title;
            $aux['photos'] = $post->product->photos->pluck('url');
            $aux['price'] = $post->actualPrice();
            $aux['endDate'] = $post->endDate;
            array_push($result,$aux);
        }
        return $result;
    }

    public function finalizado()
    {
        return Carbon::now()->subHours(3)->isBefore(Carbon::parse($this->endDate));
    }
}
