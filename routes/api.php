<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('forms')->group(function () {
            Route::post('', [FormController::class, 'createForm']);
            Route::get('', [FormController::class, 'getAllForm']);
            Route::get('{slug}', [FormController::class, 'getDetailForm']);

            Route::post('{slug}/questions', [QuestionController::class, 'addQuestion']);
            Route::delete('{slug}/questions/{id}', [QuestionController::class, 'removeQuestion']);

            Route::post('{slug}/responses', [ResponseController::class, 'submitResponse']);
            Route::get('{slug}/responses', [ResponseController::class, 'getResponses']);
        });

    });
});
