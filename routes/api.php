<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::post('change-password', [AuthController::class, 'changePassword']);
