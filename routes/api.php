<?php

use App\Http\Controllers\Api\V1\AgentController;
use App\Http\Controllers\Api\V1\CustomerAssetController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\PropertyTypeController;
use App\Http\Controllers\Api\V1\SellerApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['agent.api'])->prefix('v1')->group(function (): void {
    Route::get('property-types', [PropertyTypeController::class, 'index'])->name('api.v1.property-types.index');

    Route::get('properties', [PropertyController::class, 'index'])->name('api.v1.properties.index');
    Route::get('properties/search', [PropertyController::class, 'search'])->name('api.v1.properties.search');

    Route::post('properties/{property}/views', [PropertyController::class, 'recordView'])->name('api.v1.properties.views.store');

    Route::get('properties/{property}', [PropertyController::class, 'show'])->name('api.v1.properties.show');

    Route::get('agents', [AgentController::class, 'index'])->name('api.v1.agents.index');

    Route::get('seller', [SellerApiController::class, 'index'])->name('api.v1.user.index');

    Route::post('customer-assets', [CustomerAssetController::class, 'store'])->name('api.v1.customer-assets.store');
});
