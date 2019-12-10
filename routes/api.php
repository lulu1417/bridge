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

Route::post('getIn','BridgeController@login');
Route::post('login','BridgeController@back');
Route::get('room','BridgeController@room');

Route::get('start','BridgeController@distribute');
Route::post('start','BridgeController@bid');
Route::get('bid','BridgeController@lastBid');

Route::get('pile','BridgeController@turnOver');
Route::post('card','BridgeController@card');

Route::post('play','BridgeController@play');
Route::get('compare','BridgeController@judge');
Route::delete('over','BridgeController@over');

Route::put('mod/{id}','BridgeController@modify');





