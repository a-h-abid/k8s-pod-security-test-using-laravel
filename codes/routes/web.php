<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return [
        'name' => config('app.name'),
    ];
});

Route::get('/call-about/{svc}/{prt}', function ($svc, $prt = 8080) {
    return response()->json(Http::get("http://{$svc}:${prt}/about"));
});
