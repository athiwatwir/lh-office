<?php

use App\Http\Controllers\Api\V1\AgentController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\PropertyTypeController;
use App\Http\Controllers\Api\V1\SellerApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['agent.api'])->prefix('v1')->group(function (): void {
    Route::get('property-types', [PropertyTypeController::class, 'index'])->name('api.v1.property-types.index');

    Route::get('properties', [PropertyController::class, 'index'])->name('api.v1.properties.index');

    Route::get('properties/{property}', [PropertyController::class, 'show'])->name('api.v1.properties.show');

    Route::get('agents', [AgentController::class, 'index'])->name('api.v1.agents.index');

    Route::get('seller', [SellerApiController::class, 'index'])->name('api.v1.user.index');
});
