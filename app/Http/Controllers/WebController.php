<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use App\Tag;
use App\Tag_Product;
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
        $productsId = Tag_Product::whereIn('tag_id', $tags)->pluck('product_id');
        $posts = Post::whereIn('product_id',$productsId)->where('active',1)->get();
        $aux = collect();

        foreach ($posts as $p) {
            $p->product->photos;
            $p->product->tags;
            $p->actualPrice = $p->actualPrice();
            $p->actualDiscount = $p->actualDiscount() * 100;
            $p->productPrice = $p->product->price;
            if($p->finalizado()){
                $aux->push($p);
            }
        }
        return $aux;
    }

    public function getPostById(Request $request)
    {
        $id =$request->id;
        $post = Post::where('id',$id)->where('active',1)->first();
        if($post) {
            $post->qtySuscriptions = $post->qtySuscriptors();
            $post->discount = number_format(100 * $post->actualDiscount(),2);
            $post->nextDiscount = number_format(100 * $post->nextDiscount(),2);
            $post->actualPrice = number_format($post->actualPrice(),2);
            $post->originalPrice = number_format($post->product->price,2);
            $post->qtyToNextDiscount = $post->qtyToNextDiscount();
            $post->nextDiscountPrice = (100 - $post->nextDiscount) * $post->product->price / 100;
            $post->recommendations = $post->recommendations();
            $post->product->photos;
        }
        return $post ?? 'error';

    }
}
