<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::post('change-password', [AuthController::class, 'changePassword']);
Route::get('check-auth', [AuthController::class, 'checkAuth']);

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/', [CourseController::class, 'create']);
    Route::get('/{id}', [CourseController::class, 'show']);
    Route::put('/{id}', [CourseController::class, 'update']);
    Route::delete('/{id}', [CourseController::class, 'delete']);
});

Route::prefix('chapters')->group(function () {
    Route::get('/', [ChapterController::class, 'index']);
    Route::post('/', [ChapterController::class, 'create']);
    Route::get('/{id}', [ChapterController::class, 'show']);
    Route::put('/{id}', [ChapterController::class, 'update']);
    Route::delete('/{id}', [ChapterController::class, 'delete']);
});

Route::prefix('lessons')->group(function () {
    Route::get('/', [LessonController::class, 'index']);
    Route::post('/', [LessonController::class, 'create']);
    Route::get('/{id}', [LessonController::class, 'show']);
    Route::put('/{id}', [LessonController::class, 'update']);
    Route::delete('/{id}', [LessonController::class, 'delete']);
    Route::get('/selected/{id}', [LessonController::class, 'getSelected']);
    Route::post('/selected/{id}', [LessonController::class, 'saveSelected']);
});

Route::prefix('students')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'create']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
});

Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index']);
    Route::post('/', [TeacherController::class, 'create']);
    Route::get('/{id}', [TeacherController::class, 'show']);
    Route::put('/{id}', [TeacherController::class, 'update']);
    Route::delete('/{id}', [TeacherController::class, 'delete']);
    Route::put('/info', [TeacherController::class, 'updateInfo']);
});

Route::prefix('questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::post('/', [QuestionController::class, 'create']);
    Route::get('/{id}', [QuestionController::class, 'show']);
    Route::put('/{id}', [QuestionController::class, 'update']);
    Route::delete('/{id}', [QuestionController::class, 'delete']);
});

Route::prefix('home')->group(function () {
    Route::get('top-courses', [HomeController::class, 'topCourses']);
    Route::get('courses', [HomeController::class, 'courses']);
    Route::get('list-lesson/{id}', [HomeController::class, 'listLesson']);
    Route::get('get-question/{id}', [HomeController::class, 'getQuestion']);
    Route::post('submit-lesson/{id}', [HomeController::class, 'submitLesson']);
    Route::get('test', [HomeController::class, 'test']);
    Route::get('get-test/{id}', [HomeController::class, 'getTest']);
    Route::post('submit-test', [HomeController::class, 'submitTest']);
    Route::get('history/{id}', [HomeController::class, 'history']);
});
