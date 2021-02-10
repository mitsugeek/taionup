<?php

use Illuminate\Support\Facades\Route;

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
    return "hello world!";
});


Route::post('line/Webhook', [App\Http\Controllers\WebhookController::class, 'webhook']);

Route::get('line/liff', [App\Http\Controllers\LiffController::class, 'home']);

Route::post('line/getUser', [App\Http\Controllers\LiffController::class, 'getUserAPI'])->name('getUser');

Route::get('line/TaionList', [App\Http\Controllers\LiffController::class, 'TaionList'] )->name('TaionList');

Route::get('line/getSessionUser', function(){
    $request = request();
    return $request->session()->all(); 
})->name('getSessionUser');


Route::get('line/userlist', [App\Http\Controllers\LiffController::class, 'userlist']);
Route::post('line/userlist/update', [App\Http\Controllers\LiffController::class, 'userUpdate'])->name('userUpdate');
