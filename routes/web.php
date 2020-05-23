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

//// Authentication Routes...
//$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
//$this->post('login', 'Auth\LoginController@login');
//$this->post('logout', 'Auth\LoginController@logout')->name('logout');
//
//// Registration Routes...
//$this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
//$this->post('register', 'Auth\RegisterController@register');
//
//// Password Reset Routes...
//$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
//$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
//$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
//$this->post('password/reset', 'Auth\ResetPasswordController@reset');

Auth::routes();

Route::get('/login-front', 'Auth\LoginController@authenticate')->name('loginFront')->middleware('cors');
Route::get('/logout-front', 'Auth\LoginController@logout')->name('logoutFront')->middleware('cors');

Route::get('/tags', 'WebController@getTags')->name('getTags')->middleware('cors');
Route::get('/categories', 'WebController@getCategories')->name('getCategories')->middleware('cors');
Route::get('/posts', 'WebController@getPostsByTags')->name('getPosts')->middleware('cors');
Route::get('/post', 'WebController@getPostById')->name('getPost')->middleware('cors');

Route::get('/home', 'HomeController@index')->name('home')->middleware('cors');
Route::get('/addProduct', 'HomeController@addProduct')->middleware('cors');
Route::get('/addSubscription', 'HomeController@addSubscription')->middleware('cors');
Route::get('/addTagProducts', 'HomeController@addTagProducts')->middleware('cors');
Route::get('/addPost', 'HomeController@addPost')->middleware('cors');
Route::get('/addProductImages', 'HomeController@addProductImages')->middleware('cors');
Route::get('/addDiscounts', 'HomeController@addDiscounts')->middleware('cors');
Route::get('/addTags', 'HomeController@addTags')->middleware('cors');
Route::get('/addSubcategoryTags', 'HomeController@addSubcategoryTags')->middleware('cors');
Route::get('/addProductCategories', 'HomeController@addProductCategories')->middleware('cors');
Route::get('/addCategory', 'HomeController@addCategory')->middleware('cors');
Route::get('/addSubcategory', 'HomeController@addSubcategory')->middleware('cors');

Route::get('/subscribe', 'HomeController@subscribe')->middleware('cors');
Route::get('/subscriptions', 'HomeController@subscriptions')->middleware('cors');



Route::get('/busquedaParaSenior', 'HomeController@busquedaParaSenior')->middleware('cors');
Route::get('/borrarParaSenior', 'HomeController@borrarParaSenior')->middleware('cors');


Route::get('/profile', function () {
    dd('Adentro');
})->middleware('auth.basic');
