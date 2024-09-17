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


Route::post('/chat/available','ChatController@available');
Route::post('/chat/chatsList','ChatController@chatsList');
Route::post('/chat/chatMessages','ChatController@chatMessages');
Route::post('/chat/send', 'ChatController@send')->name('send');



