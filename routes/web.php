<?php

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
use App\User;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    //dd(App\User::where('email','mukesh2@yopmail.com')->first());
    return view('emails/users/verification')->with(['user', App\User::where('email', 'mukesh2@yopmail.com')->first()]);
});
Route::post('/charge', 'CheckoutController@charge');
Route::get('/subscribe', function () {
    return view('subscribe');
});
Route::post('/subscribe_process', 'CheckoutController@subscribe_process');
Route::get('/invoices', 'CheckoutController@invoices');
Route::get('/invoice/{invoice_id}', 'CheckoutController@invoice');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
