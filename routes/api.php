<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('password/email',[AuthController::class,'sendResetLinkEmail']);
Route::post('password/reset',[AuthController::class,'resetPassword']);
Route::post('clear-throttle', [AuthController::class, 'clearThrottle']);

Route::middleware('auth:sanctum')->apiResource('products',ProductController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/{id}', [UserController::class, 'show']);
       //->middleware('can:view,user'); // Apply the view policy here

    Route::put('/user/{id}', [UserController::class, 'update']);
   //     ->middleware('can:update,user'); // Apply the update policy here
});
