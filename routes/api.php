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

Route::get('start','BridgeController@distribute');
Route::post('start','BridgeController@bid');

Route::get('switch','BridgeController@turnOver');

Route::post('play','BridgeController@play');
Route::get('play','BridgeController@compare');



