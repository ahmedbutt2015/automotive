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

Route::post('/save/user/info', 'UserController@save');
Route::post('/start', 'UserController@start');
Route::get('/', function () {
    return view('welcome');
});
Route::get('/start', function () {
    return view('start');
});
