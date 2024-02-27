<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\PostController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::get('getallusers', [UserController::class, 'getAllUsers']);





Route::middleware('auth:api')->group(function (){
    
  
Route::post('like', [LikeController::class, 'likeProfile']);
Route::post('reset', [UserController::class, 'reset']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
       
});
