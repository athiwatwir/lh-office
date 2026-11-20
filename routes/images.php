<?php

use App\Http\Controllers\ImageCacheController;
use App\Http\Controllers\ImageProxyController;
use Illuminate\Support\Facades\Route;

/*
| Image routes intentionally avoid the "web" middleware group
| (no session/cookies/CSRF) so cached images stay fast.
*/

Route::get('/img-cache/{image}_{variant}.webp', [ImageCacheController::class, 'show'])
    ->where([
        'image' => '[0-9a-fA-F-]{36}',
        'variant' => 'thumb|gallery',
    ])
    ->name('image.cache');

Route::get('/img/{image}', [ImageProxyController::class, 'show'])
    ->where('image', '[0-9a-fA-F-]{36}')
    ->name('image.proxy');
