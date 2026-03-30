<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniversityController;

Route::post('/university/login', [UniversityController::class, 'login']);
Route::get('/university/schedule/{user_id}', [UniversityController::class, 'getSchedule']);
Route::get('/university/students', [UniversityController::class, 'getAllStudents']);