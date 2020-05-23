<?php

namespace App\Http\Controllers;

use App\Category;
use App\Discount;
use App\Post;
use App\Product;
use App\Subcategory;
use App\Subscription;
use App\Tag;
use App\Tag_Product;
use DateTime;
use Illuminate\Http\Request;

class WebController extends Controller
{

    public function getTags()
    {
        return Tag::all();
    }

    public function getCategories()
    {
        $categories = Category::with('subcategories')->get();

        foreach ($categories as $c) {
            foreach ($c->subcategories as $s) {
                $s->tags;
            }
        }

        return $categories;
    }

    public function getPostsByTags(Request $request)
    {
        $tags = explode(",",$request['tags']);
//        $filters = $request->filtros;

        $productsId = Tag_Product::whereIn('tag_id', $tags)->pluck('product_id');
//        if($filters){
//            $productsId = $productsId->where(asdasdas)
//        }

        $posts = Post::whereIn('product_id',$productsId)->get();

        foreach ($posts as $p) {
            $p->product->photos;
            $p->product->tags;
        }
        return $posts;
    }

    public function getPostById(Request $request)
    {
        $id =$request->id;

        $post = Post::where('id',$id)
                ->first();

        if($post) {
            $post->qtySuscriptions = $post->qtySuscriptors();
            $post->discount = $post->actualDiscount();
            $post->nextDiscount = $post->nextDiscount();
            $post->actualPrice = $post->actualPrice();
            $post->originalPrice = $post->product->price;
            $post->qtyToNextDiscount = $post->qtyToNextDiscount();
            $post->nextDiscountPrice = $post->nextDiscount * $post->product->price;
            $post->recommendations = $post->recommendations();
            $post->product->photos;
        }

        return $post ?? 'error';

    }
}