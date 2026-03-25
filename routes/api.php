<?php

use App\Http\Controllers\Api\AdminActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/me', [AuthController::class, 'me']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::post('/upload', [UploadController::class, 'store']);

Route::get('/admin/activity', [AdminActivityController::class, 'index']);
Route::match(['patch', 'post'], '/admin/landing', [LandingPageController::class, 'update']);

Route::get('/landing', [LandingPageController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/categories', [ProductController::class, 'categories']);
Route::get('/products/new', [ProductController::class, 'new']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::patch('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
