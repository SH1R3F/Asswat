<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::get('me', 'Api\Auth\LoginController@me');
    Route::post('login', 'Api\Auth\LoginController@login')->name('auth.login');
    Route::post('logout', 'Api\Auth\LoginController@logout');
    Route::post('refresh', 'Api\Auth\LoginController@refresh');
    Route::post('register', 'Api\Auth\RegisterController@register')->name('auth.register');
});


Route::group(['prefix' => 'messages'], function () {
    Route::get('/', 'Api\MessagesController@index');
});

Route::get('/{username}', 'Api\UsersController@show');
Route::post('/{username}/send', 'Api\MessagesController@store');

Route::fallback(function () {
    abort(404);
});