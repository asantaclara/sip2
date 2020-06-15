<?php

namespace App\Http\Controllers;

use App\Category;
use App\Discount;
use App\Post;
use App\Product;
use App\Product_Category;
use App\Product_Image;
use App\Subcategory;
use App\Subcategory_Tags;
use App\Subscription;
use App\Tag;
use App\Tag_Product;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Result;

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
            $post = Post::where('id', $request['post_id'])->where('active',1)->get()->first();
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
                    'productTitle' => $s->first()->post->product->title,
                    'finalizada' => !$s->first()->post->finalizado(),
                    'giftCard' => $s->first()->giftCard == 1,
                    'alert' => count(Carbon::now()->subHours(3)->minutesUntil(Carbon::parse($s->first()->post->endDate))) < 60,
                ];
                $result->push($aux);
            }
            $aux2 = [
                'beforeDiscount' => $result->where('finalizada',0)->sum('totalPrice'),
                'discount' => $result->where('finalizada',0)->sum('totalDiscountAmount'),
                'totalPaid' => $result->where('finalizada',0)->sum('paid'),
                'total' => $result->where('finalizada',0)->sum('totalPrice') -
                    $result->where('finalizada',0)->sum('totalDiscountAmount') -
                    $result->where('finalizada',0)->sum('paid'),
            ];
            $sortedScores = Arr::sort($result, function($student)
            {
                return $student['endDate'];
            });
            $finalizadas = [];
            $activas = [];
            foreach ($sortedScores as $s) {
                if($s['finalizada']) {
                    array_push($finalizadas,$s);

                } else {
                    array_push($activas,$s);
                }
            }
            return [$activas, $aux2,$finalizadas];
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function askForGiftCard(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 0) {
            $post = Post::where('id',$request['post'])->where('active',1)->first();
            if(!$post) {
                return response()->json(['error' => 'Post not found', 404]);
            }

            if(!Carbon::now()->isBefore(Carbon::parse($post->endDate)->addDays(2))) {
                return response()->json(['error' => 'More than 48hrs later', 404]);
            }

            if(Carbon::now()->isBefore(Carbon::parse($post->endDate))) {
                return response()->json(['error' => 'Post not finished', 404]);
            }

            $subscriptions = Subscription::where('post_id', $post->id)->where('user_id',$user->id)->get();

            foreach ($subscriptions as $subscription){
                $subscription->giftCard = 1;
                $subscription->save();
            }
            return $subscriptions;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function userHasSubscription(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 0) {
            $post = Post::where('id', $request['post_id'])->first();
            if(!$post) {
                return response()->json(['error' => 'Post not found', 404]);
            }

            $subscriptions = Subscription::where('post_id', $request['post_id'])->where('user_id', $user->id)->first();

            if(!$subscriptions) {
                return false;
            } else {
                return true;
            }
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }
    //--------------------------------------BackOffice------------------------------------------------------------------
    public function graphs(Request $request)
    {
        $user = $this->checkLogIn($request['token']);
        //TODO hacer que los graficos tengan valores posta

        if($user && $user->role == 1) {
            $data = [
                [
                    'title' => 'Ventas del mes',
                    'xlabel' => 'Semana',
                    'ylabel' => 'Ventas (AR$)',
                    'data' => [
                        ['x' => '1', 'y' => 570000],
                        ['x' => '2', 'y' => 470000],
                        ['x' => '3', 'y' => 370000],
                        ['x' => '4', 'y' => 270000],
                    ]
                ],
                [
                    'title' => 'Ultimos 6 meses',
                    'xlabel' => 'Fecha',
                    'ylabel' => 'Ventas (AR$)',
                    'data' => [
                        ['x' => 'Ene', 'y' => 570000],
                        ['x' => 'Feb', 'y' => 470000],
                        ['x' => 'Mar', 'y' => 370000],
                        ['x' => 'Abr', 'y' => 270000],
                        ['x' => 'May', 'y' => 170000],
                        ['x' => 'Jun', 'y' => 570000],
                        ['x' => 'Jul', 'y' => 570000],
                    ]
                ],
                [
                    'title' => 'Venta Anual 2019',
                    'xlabel' => 'Fecha',
                    'ylabel' => 'Ventas (AR$)',
                    'data' => [
                        ['x' => 'Ene', 'y' => 570000],
                        ['x' => 'Feb', 'y' => 470000],
                        ['x' => 'Mar', 'y' => 370000],
                        ['x' => 'Abr', 'y' => 270000],
                        ['x' => 'May', 'y' => 170000],
                        ['x' => 'Jun', 'y' => 570000],
                        ['x' => 'Jul', 'y' => 570000],
                        ['x' => 'Ago', 'y' => 570000],
                        ['x' => 'Sep', 'y' => 470000],
                        ['x' => 'Oct', 'y' => 370000],
                        ['x' => 'Nov', 'y' => 270000],
                        ['x' => 'Dic', 'y' => 170000],
                    ]
                ]
            ];
            return $data;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function backOfficeTable(Request $request)
    {
        $user = $this->checkLogIn($request['token']);
        if($user && $user->role == 1) {
            $data = [
                [
                    ['id' => 'foto', 'label' => 'Foto'],
                    ['id' => 'nombre', 'label' => 'Nombre'],
                    ['id' => 'precio', 'label' => 'Precio'],
                    ['id' => 'precioActual', 'label' => 'Precio Actual'],
                    ['id' => 'categorias', 'label' => 'Categorias'],
                    ['id' => 'activo', 'label' => 'Activo'],
                    ['id' => 'suscriptos', 'label' => 'Suscriptos'],
                    ['id' => 'fecha', 'label' => 'Fecha Cierre']
                ],
                []
            ];
            $posts = Post::all()->sortByDesc('created_at');
            foreach ($posts as $p) {
                $aux = [
                    'id' => $p->id,
                    'foto' => $p->product->photos->first()->url,
                    'nombre' => $p->product->title,
                    'precio' => '$'.number_format($p->product->price,2),
                    'precioActual' => '$'.number_format($p->actualPrice(),2),
                    'categorias' => $p->product->tags->first()->name,
                    'activo' => $p->active == 1,
                    'suscriptos' => $p->qtySuscriptors(),
                    'fecha' => Carbon::parse($p->endDate)->format('d/m/yy'),
                ];
                array_push($data[1],$aux);
            }
            return $data;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function products(Request $request)
    {
        $user = $this->checkLogIn($request['token']);
        if($user && $user->role == 1) {
            $products = Product::all();
            foreach ($products as $p) {
                $p->photos;
            }
            return $products;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function changeStatePost(Request $request)
    {
        $user = $this->checkLogIn($request['token']);
        if($user && $user->role == 1) {
            $post = Post::where('id',$request['post_id'])->get()->first();
            if(!$post) {
                return response()->json(['error' => 'Post not found', 404]);
            }
            $post->active = $request['state'];
            $post->save();
            return $post;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    public function card(Request $request)
    {
        $user = $this->checkLogIn($request['token']);
        if($user && $user->role == 1) {


            $posts = Post::where('endDate', '>=', Carbon::parse($request['date'])->startOfDay()->toDateTimeString())
                ->where('endDate', '<=', Carbon::parse($request['date'])->endOfDay()->toDateTimeString())
                ->get();
            $total = 0;
            foreach ($posts as $p) {
                $total = $total + $p->qtySuscriptors() * $p->actualPrice();
            }
            $data = [
                'type' => 'Facturacion del dia',
                'value' => '$'.number_format($total,2),
                'date' => $request['date'],
                'action' => 'Ver detalle'
            ];
            return $data;
        } else {
            return response()->json(['error' => 'Forbidden', 403]);
        }
    }

    //--------------------------------------Administracion de productos ------------------------------------------------
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

    public function createNewPost(Request $request)
    {
        $user = $this->checkLogIn($request['token']);
        $user = User::where('id',1)->get()->first();
        if($user && $user->role == 1) {

            $percentage = explode("|", $request['percentage']);
            $qty = explode("|", $request['qty']);

            if(count($percentage) != count($qty)) {
                return response()->json(['error' => 'Incompatible size', 404]);
            }
            $post = Post::create([
               'product_id' => $request['product_id'],
               'startDate' => $request['startDate'],
               'endDate' => Carbon::parse($request['startDate'])->addHours($request['duration']),

           ]);

            for ($i = 0 ; $i < count($percentage) ; $i++) {
               Discount::create([
                   'post_id' => $post->id,
                   'quantityStart' => $qty[$i],
                   'discount' => $percentage[$i]
               ]);
            }

            return [$post, Discount::where ('post_id',$post->id)->get()];
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

    public function addCategory(Request $request)
    {
        $user = $this->checkLogIn($request['token']);

        if($user && $user->role == 1) {
            $subscription = Category::create([
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
            $subscription = Subcategory::create([
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

//    public function addProductCategories(Request $request)
//    {
//        $user = $this->checkLogIn($request['token']);
//
//        if($user && $user->role == 1) {
//            $subscription = Product_Category::create([
//                'category_id' => $request['category_id'],
//                'product_id' => $request['product_id'],
//            ]);
//            return $subscription;
//        } else {
//            return response()->json(['error' => 'Forbidden', 403]);
//        }
//    }
}
