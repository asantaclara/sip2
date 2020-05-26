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
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }
    private function checkLogIn($token)
    {
       return User::where('remember_token', $token)->first();
    }
    public function subscribe(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 0) {
            $qty = floor($request['qty']);
            $post = Post::where('id', $request['post_id'])->get()->first();
            if(!$post) {
                return response()->json(['error' => 'Post not found', 406]);

            }
            if($qty < 1){
                return response()->json(['error' => 'quantity < 1', 406]);
            }
            $subscription = Subscription::create([
                'post_id' => $request['post_id'],
                'user_id' => $user->id,
                'dateTime' => Carbon::now(),
                'bookingPrice' => $post->actualPrice(),
                'quantity' => $qty,
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function subscriptions(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 0) {
            $result = collect();
            $subscriptions = Subscription::where('user_id', $user->id)->get()->groupBy('post_id');
            if(count($subscriptions) < 1) {
                return response()->json(['error' => 'No subscriptions found, 404']);
            }
            foreach ($subscriptions as $s) {
                $aux = [
                    'post_id' => $s->first()->post_id,
                    'totalPrice' => $s->first()->post->product->price * $s->sum('quantity'),
                    'totalActualPrice' => $s->first()->post->actualPrice() * $s->sum('quantity'),
                    'price' => $s->first()->post->product->price,
                    'actualPrice' => $s->first()->post->actualPrice(),
                    'quantity' => $s->sum('quantity'),
                    'discountPercent' => $s->first()->post->actualDiscount(),
                    'discountAmount' => round($s->first()->post->product->price - $s->first()->post->actualPrice(),2),
                    'totalDiscountAmount' => round(($s->first()->post->product->price - $s->first()->post->actualPrice()) * $s->sum('quantity'),2),
                    'paid' => round($s->sum(function ($a) {return $a->bookingPrice * $a->quantity;}) * 0.1,2),
                    'endDate' => $s->first()->post->endDate,
                    'photos' => $s->first()->post->product->photos,
                    'productTitle' => $s->first()->post->product->title
                ];
                $result->push($aux);
            }
            $aux2 = [
                'beforeDiscount' => $result->sum('totalPrice'),
                'discount' => $result->sum('totalDiscountAmount'),
                'totalPaid' => $result->sum('paid'),
                'total' => $result->sum('totalPrice') - $result->sum('totalDiscountAmount') - $result->sum('paid'),
            ];
            return [$result, $aux2];
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    //--------------------------------------Administracion de productos -----------------------------------
    public function addProduct(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $product = Product::create([
                'title' => $request['title'],
                'description' => $request['description'],
                'price' => $request['price']
            ]);
            return $product;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addSubscription(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Subscription::create([
                'post_id' => $request['post_id'],
                'user_id' => $request['user_id'],
                'dateTime' => $request['dateTime'],
                'bookingPrice' => $request['bookingPrice'],
                'quantity' => $request['quantity'],
                'giftCard' => $request['giftCard']
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addTagProducts(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Tag_Product::create([
                'tag_id' => $request['tag_id'],
                'product_id' => $request['product_id'],
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addPost(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Post::create([
            'product_id' => $request['product_id'],
            'startDate' => $request['startDate'],
            'endDate' => $request['endDate'],
            ]);
        return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addProductImages(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Product_Image::create([
                'product_id' => $request['product_id'],
                'url' => $request['url'],
            ]);
        return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addDiscounts(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Discount::create([
                'post_id' => $request['post_id'],
                'quantityStart' => $request['quantityStart'],
                'discount' => $request['discount'],
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addTags(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Tag::create([
                'name' => $request['name'],
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addSubcategoryTags(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Subcategory_Tags::create([
                'subcategory_id' => $request['subcategory_id'],
                'tag_id' => $request['tag_id'],
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addProductCategories(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Product_Category::create([
                'category_id' => $request['category_id'],
                'product_id' => $request['product_id'],
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addCategory(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Product_Category::create([
                'title' => $request['title'],
            ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function addSubcategory(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Product_Category::create([
            'title' => $request['title'],
            'category_id' => $request['category_id'],
        ]);
            return $subscription;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function busquedaParaSenior(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            return DB::select( DB::raw('SELECT * FROM '.$request['tabla']) );
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function borrarParaSenior(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            return DB::select( DB::raw('DELETE FROM '.$request['tabla'].' WHERE ID = '.$request['id']) );
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

//    public function armarDescuentos(Request $request)
//    {
//        $posts = Post::all();
//
//        foreach ($posts as $p){
//            $discount = 0.05;
//            $qty = 0;
//
//            for ( $i = 1 ; $i <= random_int(4,6); $i++) {
//                Discount::create([
//                    'post_id' => $p->id,
//                    'quantityStart' => $qty,
//                    'discount' => $discount,
//                ]);
//                $qty += random_int(10,20);
//                $discount += random_int(5,7) / 100;
//            }
//         }
//    }
}
