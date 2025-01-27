<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\TokenVerificationController;
use App\Http\Controllers\UserController;
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

// Public Routes (CORS Middleware applied globally or on specific routes)
Route::middleware(['cors'])->group(function () {
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot_password']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset_password']);
});

// Logged-in users only
Route::middleware(['auth:sanctum', 'cors'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/send-code', [TokenVerificationController::class, 'send_code'])->middleware('throttle:5,10');
    Route::post('/verify-code', [TokenVerificationController::class, 'verify_code'])->middleware('throttle:10,5');

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});

// Verified sessions only
Route::middleware('auth:sanctum', 'token-verified')->group(function () {

    Route::post('events/{event}/scans_stream', [ScanController::class, 'stream']);
    Route::apiResource('events', EventController::class)->only(['index', 'show']);

    // Guests
    Route::get('events/{event}/guests/download', [GuestController::class, 'download']);
    Route::post('events/{event}/guests/upload', [GuestController::class, 'upload']);
    Route::apiResource('events.guests', GuestController::class);

    // Invitations
    Route::post('events/{event}/attend', [InvitationController::class, 'attend']);
    Route::get('events/{event}/scans', [ScanController::class, 'index']);

    // Admins & Managers only
    Route::middleware('role:admin,manager')->group(function () {
        // Events
        Route::apiResource('events', EventController::class)->except(['index', 'show']);

        // Invitations
        Route::post('events/{event}/guests/invite-selected', [InvitationController::class, 'invite_selected']);
        Route::post('events/{event}/guests/invite-all', [InvitationController::class, 'invite_all']);
        Route::post('events/{event}/guests/{guest}/invite', [InvitationController::class, 'invite']);
    });

    // Admins only
    Route::middleware('role:admin')->group(function () {
        // Users
        Route::apiResource('users', UserController::class);
    });
});
