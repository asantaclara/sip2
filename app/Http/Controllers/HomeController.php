<?php

namespace App\Http\Controllers;

use App\Category;
use App\Discount;
use App\Post;
use App\Product;
use App\Product_Category;
use App\Product_Image;
use App\Subcategory_Tags;
use App\Subscription;
use App\Tag;
use App\Tag_Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function addProduct(Request $request)
    {
        $product = Product::create([
            'title' => $request['title'],
            'description' => '{}',
            'price' => $request['price']
        ]);

        return $product;
    }

    public function addSubscription(Request $request)
    {
        $subscription = Subscription::create([
            'post_id' => $request['post_id'],
            'user_id' => $request['user_id'],
            'dateTime' => $request['dateTime'],
            'bookingPrice' => $request['bookingPrice'],
            'quantity' => $request['quantity'],
            'giftCard' => $request['giftCard']
        ]);

        return $subscription;
    }

    public function addTagProducts(Request $request)
    {
        $subscription = Tag_Product::create([
            'tag_id' => $request['tag_id'],
            'product_id' => $request['product_id'],
        ]);

        return $subscription;
    }

    public function addPost(Request $request)
    {
        $subscription = Post::create([
            'product_id' => $request['product_id'],
            'startDate' => $request['startDate'],
            'endDate' => $request['endDate'],
        ]);

        return $subscription;
    }

    public function addProductImages(Request $request)
    {
        $subscription = Product_Image::create([
            'product_id' => $request['product_id'],
            'url' => $request['url'],
        ]);

        return $subscription;
    }

    public function addDiscounts(Request $request)
    {
        $subscription = Discount::create([
            'post_id' => $request['post_id'],
            'quantityStart' => $request['quantityStart'],
            'discount' => $request['discount'],
        ]);

        return $subscription;
    }

    public function addTags(Request $request)
    {
        $subscription = Tag::create([
            'name' => $request['name'],
        ]);

        return $subscription;
    }

    public function addSubcategoryTags(Request $request)
    {
        $subscription = Subcategory_Tags::create([
            'subcategory_id' => $request['subcategory_id'],
            'tag_id' => $request['tag_id'],
        ]);

        return $subscription;
    }

    public function addProductCategories(Request $request)
    {
        $subscription = Product_Category::create([
            'category_id' => $request['category_id'],
            'product_id' => $request['product_id'],
        ]);

        return $subscription;
    }

    public function addCategory(Request $request)
    {
        $subscription = Product_Category::create([
            'title' => $request['title'],
        ]);

        return $subscription;
    }

    public function addSubcategory(Request $request)
    {
        $subscription = Product_Category::create([
            'title' => $request['title'],
            'category_id' => $request['category_id'],
        ]);

        return $subscription;
    }

    public function busquedaParaSenior(Request $request)
    {
        return DB::select( DB::raw('SELECT * FROM '.$request['tabla']) );
    }

    public function borrarParaSenior(Request $request)
    {
        return DB::select( DB::raw('DELETE FROM '.$request['tabla'].' WHERE ID = '.$request['id']) );
    }
}
