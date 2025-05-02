<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return [
        'user' => 'johnny test'
    ];
});

Route::post('/chunk', function (Request $request) {
    return [
        'request' => $request->all(),
    ];
});