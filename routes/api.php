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

Route::post('login','BridgeController@login');
Route::get('start','BridgeController@distribute');
Route::post('start','BridgeController@bid');

Route::get('pile','BridgeController@turnOver');
Route::get('card','BridgeController@card');

Route::post('play','BridgeController@play');
Route::post('compare','BridgeController@judge');
Route::delete('over','BridgeController@over');





