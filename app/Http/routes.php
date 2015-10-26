<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//For testing only
//Route::get('/', 'TestController@index');

Route::get('/', function () {
    return view('errors/503');
});

Route::any('{all}', function()
{
    return Redirect::to('/');
})->where('all', '.*');
