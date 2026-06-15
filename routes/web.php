<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorImageController;
use App\Http\Controllers\ActiveAgentController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyImageController;
use App\Http\Controllers\PropertyRequestController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::post('active-agent', [ActiveAgentController::class, 'store'])->name('active-agent.store');
    Route::delete('active-agent', [ActiveAgentController::class, 'destroy'])->name('active-agent.destroy');
    Route::post('editor/upload-image', [EditorImageController::class, 'store'])->name('editor.upload-image');

    Route::resources([
        'propertyRequest' => PropertyRequestController::class,
        'property' => PropertyController::class,
        'propertyType' => PropertyTypeController::class,
        'user' => UserController::class,
        'agent' => AgentController::class,
    ], ['except' => ['show']]);

    Route::put('user/{user}/password', [UserController::class, 'updatePassword'])->name('user.password.update');
    Route::patch('property/{property}/isactive', [PropertyController::class, 'updateIsactive'])->name('property.isactive.update');
    Route::patch('property/{property}/agent', [PropertyController::class, 'transferAgent'])->name('property.agent.update');
    Route::get('property/{property}', [PropertyController::class, 'show'])->name('property.show');
    Route::get('propertyRequest/{propertyRequest}', [PropertyRequestController::class, 'show'])->name('propertyRequest.show');
    Route::post('property/{property}/images', [PropertyImageController::class, 'store'])->name('property.images.store');
    Route::patch('property/{property}/images/{assetImage}/default', [PropertyImageController::class, 'setDefault'])->name('property.images.default');
    Route::delete('property/{property}/images/{assetImage}', [PropertyImageController::class, 'destroy'])->name('property.images.destroy');
});

require __DIR__ . '/auth.php';
