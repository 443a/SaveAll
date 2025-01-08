<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);

Route::get('/', function () {
    return view('welcome');
});
