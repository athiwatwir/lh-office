<?php

use App\Http\Controllers\EditorImageController;
use App\Http\Controllers\ActiveAgentController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__ . '/auth.php';
