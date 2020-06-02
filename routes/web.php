<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tags', 'WebController@getTags');
Route::get('/categories', 'WebController@getCategories');
Route::get('/posts', 'WebController@getPostsByTags');
Route::get('/post', 'WebController@getPostById');

Route::get('/login-front', 'Auth\LoginController@authenticate');
Route::get('/logout-front', 'Auth\LoginController@logout');


Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/addProduct', 'HomeController@addProduct');
Route::get('/addProductImages', 'HomeController@addProductImages');
Route::get('/addTagProducts', 'HomeController@addTagProducts');
Route::get('/addPost', 'HomeController@addPost');
Route::get('/addDiscounts', 'HomeController@addDiscounts');
Route::get('/addTags', 'HomeController@addTags');
Route::get('/addSubcategoryTags', 'HomeController@addSubcategoryTags');
Route::get('/addCategory', 'HomeController@addCategory');
Route::get('/addSubcategory', 'HomeController@addSubcategory');
Route::get('/graphs', 'HomeController@graphs');
Route::get('/askForGiftCard', 'HomeController@askForGiftCard');

Route::get('/subscribe', 'HomeController@subscribe');
Route::get('/subscriptions', 'HomeController@subscriptions');

//Route::get('/addProductCategories', 'HomeController@addProductCategories');
//Route::get('/armarDescuentos', 'HomeController@armarDescuentos')->middleware('cors');
//Route::get('/addSubscription', 'HomeController@addSubscription');
//Route::get('/busquedaParaSenior', 'HomeController@busquedaParaSenior')->middleware('cors');
//Route::get('/borrarParaSenior', 'HomeController@borrarParaSenior')->middleware('cors');


Route::get('/profile', function () {
    dd('Adentro');
})->middleware('auth.basic');
