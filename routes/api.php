<?php

use App\Http\Controllers\Api\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use KodePandai\ApiResponse\ApiResponse;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// TBD: protect this route with api:auth middleware?
Route::prefix('api')->name('api.')->middleware('api')->group(function () {
    //
    Route::get('version', fn () => ApiResponse::success([
        'version' => config('app.version'),
    ]))->name('version');

    Route::post('/users', [UserApiController::class, 'store'])
        ->name('users.store');

    Route::get('/users/{user}', [UserApiController::class, 'show'])
        ->name('users.show');

    Route::put('/users/{user}', [UserApiController::class, 'update'])
        ->name('users.update');

    Route::delete('/users/{user}', [UserApiController::class, 'destroy'])
        ->name('users.destroy');
    //
});
