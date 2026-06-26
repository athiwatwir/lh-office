<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorImageController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ActiveAgentController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyImageController;
use App\Http\Controllers\PropertyRequestController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\PropertyViewRankingController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/img/{image}', [ImageProxyController::class, 'show'])->name('image.proxy');

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data/summary', [DashboardController::class, 'summary'])->name('dashboard.api.summary');
    Route::get('/dashboard/data/top-views', [DashboardController::class, 'topViews'])->name('dashboard.api.top-views');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::post('active-agent', [ActiveAgentController::class, 'store'])->name('active-agent.store');
    Route::delete('active-agent', [ActiveAgentController::class, 'destroy'])->name('active-agent.destroy');
    Route::post('editor/upload-image', [EditorImageController::class, 'store'])->name('editor.upload-image');

    Route::patch('user/reorder', [UserController::class, 'reorder'])->name('user.reorder');

    Route::resources([
        'propertyRequest' => PropertyRequestController::class,
        'property' => PropertyController::class,
        'propertyType' => PropertyTypeController::class,
        'zone' => ZoneController::class,
        'user' => UserController::class,
        'agent' => AgentController::class,
        'category' => CategoryController::class,
        'article' => ArticleController::class,
    ], ['except' => ['show']]);

    Route::get('property-views', [PropertyViewRankingController::class, 'index'])->name('property.views.index');
    Route::put('user/{user}/password', [UserController::class, 'updatePassword'])->name('user.password.update');
    Route::get('property/check-code', [PropertyController::class, 'checkCode'])->name('property.check-code');
    Route::patch('property/{property}/isactive', [PropertyController::class, 'updateIsactive'])->name('property.isactive.update');
    Route::patch('property/{property}/isrecommend', [PropertyController::class, 'updateIsrecommend'])->name('property.isrecommend.update');
    Route::patch('property/{property}/agent', [PropertyController::class, 'transferAgent'])->name('property.agent.update');
    Route::get('article/{article}', [ArticleController::class, 'show'])->name('article.show');
    Route::get('property/{property}', [PropertyController::class, 'show'])->name('property.show');
    Route::get('propertyRequest/{propertyRequest}', [PropertyRequestController::class, 'show'])->name('propertyRequest.show');
    Route::post('property/{property}/images', [PropertyImageController::class, 'store'])->name('property.images.store');
    Route::patch('property/{property}/images/{assetImage}/default', [PropertyImageController::class, 'setDefault'])->name('property.images.default');
    Route::delete('property/{property}/images/{assetImage}', [PropertyImageController::class, 'destroy'])->name('property.images.destroy');
});

require __DIR__ . '/auth.php';
