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



Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');

Route::get('/', 'ProductController@index')->name('product');
Route::get('/product', 'ProductController@index')->name('product');
Route::get('/inoutp', 'ProductController@inoutp')->name('inoutp');
Route::get('/importp', 'ProductController@import')->name('importp');
Route::post('/importp', 'ProductController@import')->name('importp');

Route::post('/inoutadjust', 'ProductController@inoutadjust')->name('inoutadjust');
