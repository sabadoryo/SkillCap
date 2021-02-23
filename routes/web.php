<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

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

Route::get('/hi', [TestController::class, 'sendHelloMessage']);
Route::get('/bye', [TestController::class, 'sendByeMessage']);
Route::post('/getUpdate', [TelegramBotController::class, 'getUpdate']);
Route::get('/deleteWebhook', [TestController::class, 'removeWebhook']);
Route::get('/setWebhook', [TestController::class, 'setWebhook']);


Route::get('/sendNotification', [TelegramBotController::class, 'sendNotification']);
