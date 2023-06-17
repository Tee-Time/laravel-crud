<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ArticleController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//create
Route::post('/articles', [ArticleController::class, 'create']);

//create
Route::patch('/articles/{id}', [ArticleController::class, 'patch']);

//delete
Route::delete('/articles/{id}', [ArticleController::class, 'delete']);

//list is working
Route::get('/articles', [ArticleController::class, 'index']);
