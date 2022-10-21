<?php

use App\Http\Controllers\Web\AuthenticationWebController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('send-email', function (Request $request) {
    return [
        $request->all(),
        $_SERVER,
    ];
});

Route::middleware('web')->group(function () {
    //
    Route::redirect('/', 'login');

    Route::view('register', 'register')
        ->name('register');

    Route::post('register', [AuthenticationWebController::class, 'register']);

    Route::view('login', 'login')
        ->middleware(RedirectIfAuthenticated::class)
        ->name('login');

    Route::post('login', [AuthenticationWebController::class, 'login']);

    Route::post('logout', [AuthenticationWebController::class, 'logout'])
        ->name('logout');

    Route::middleware('auth:web')->group(function () {
        //
        Route::view('dashboard', 'dashboard')
            ->name('dashboard');
    });
});
