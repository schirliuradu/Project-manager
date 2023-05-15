<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
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
    Route::get('/projects/{project}', [ProjectController::class, 'getProject']);
    Route::post('/projects', [ProjectController::class, 'addProject']);
    Route::patch('/projects/{project}', [ProjectController::class, 'updateProject']);
    Route::patch('/projects/{project}/{action}', [ProjectController::class, 'updateProjectStatus']);
    Route::get('/projects/{project}/tasks', [TaskController::class, 'getProjectTasks']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'addTaskToProject']);
    Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'getProjectTask']);
});
