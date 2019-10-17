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


Route::get('/', function () {
    return view('welcome');
});

Route::get('lessons', 'LessonsController@index') -> name('lessons');
Route::get('callback', 'HomeController@index') -> name('callback.verify');
Route::post('callback', 'HomeController@index') -> name('callback.verify');

Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
