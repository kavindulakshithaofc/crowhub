<?php

use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function (): void {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);

    Route::post('inquiries', [InquiryController::class, 'store'])
        ->middleware('throttle:10,1');
});
