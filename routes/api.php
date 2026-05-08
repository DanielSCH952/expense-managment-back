<?php

use App\Http\Controllers\Api\HouseholdMemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HouseholdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/logout', [AuthController::class, 'logout']);

            Route::put('/profile', [AuthController::class, 'updateProfile']);

            Route::put('/password', [AuthController::class, 'updatePassword']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('households', HouseholdController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::get('households/{household}/members', [HouseholdMemberController::class, 'index']);
        Route::put('households/{household}/members/{user}', [HouseholdMemberController::class, 'update']);
        Route::delete('households/{household}/members/{user}', [HouseholdMemberController::class, 'destroy']);
        Route::post('households/{household}/leave', [HouseholdMemberController::class, 'leave']);
        Route::post('/households/join', [HouseholdMemberController::class, 'joinByCode']);
        Route::get('/households/{household}/invitation', [HouseholdMemberController::class, 'invitation']);
        Route::post('/households/{household}/regenerate-code', [HouseholdMemberController::class, 'regenerateCode']);
    });
});
