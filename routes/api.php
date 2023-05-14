<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
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

// Login route
Route::post('/login', [AuthController::class, 'login']);

Route::group([
    'middleware' => 'jwt'
], function () {
    Route::get('/projects', [ProjectController::class, 'getProjects']);
    Route::post('/projects', [ProjectController::class, 'addProject']);
});
