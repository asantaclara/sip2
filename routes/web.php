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

Route::get('/loginFront', 'Auth\LoginController@authenticate')->name('loginFront');
Route::get('/logoutFront', 'Auth\LoginController@logout')->name('logoutFront');

Route::get('/tags', 'WebController@getTags')->name('getTags');
Route::get('/categories', 'WebController@getCategories')->name('getCategories');
Route::get('/posts', 'WebController@getPostsByTags')->name('getPosts');
Route::get('/post', 'WebController@getPostById')->name('getPost');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/addProduct', 'HomeController@addProduct');
Route::get('/addSubscription', 'HomeController@addSubscription');
Route::get('/addTagProducts', 'HomeController@addTagProducts');
Route::get('/addPost', 'HomeController@addPost');
Route::get('/addProductImages', 'HomeController@addProductImages');
Route::get('/addDiscounts', 'HomeController@addDiscounts');
Route::get('/addTags', 'HomeController@addTags');
Route::get('/addSubcategoryTags', 'HomeController@addSubcategoryTags');
Route::get('/addProductCategories', 'HomeController@addProductCategories');
Route::get('/addCategory', 'HomeController@addCategory');
Route::get('/addSubcategory', 'HomeController@addSubcategory');
Route::get('/busquedaParaSenior', 'HomeController@busquedaParaSenior');
Route::get('/borrarParaSenior', 'HomeController@borrarParaSenior');


Route::get('/profile', function () {
    dd('Adentro');
})->middleware('auth.basic');
