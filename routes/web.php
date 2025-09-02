<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordleController;


Route::controller(WordleController::class)
->group(function () {

    Route::get('/', 'fetch')
        ->name('.home');

    Route::post('/submit', 'submit')
        ->name('.submit');

});
