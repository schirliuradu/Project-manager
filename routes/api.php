<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
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

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/register', [AuthController::class, 'register']);

Route::group([
    'middleware' => 'jwt'
], function () {

    // project routes
    Route::get('/projects', [ProjectController::class, 'getProjects']);
    Route::get('/projects/{project}', [ProjectController::class, 'getProject']);
    Route::post('/projects', [ProjectController::class, 'addProject']);
    Route::patch('/projects/{project}', [ProjectController::class, 'updateProject']);
    Route::patch('/projects/{project}/{action}', [ProjectController::class, 'updateProjectStatus']);
    Route::delete('/projects/{project}/{type}', [ProjectController::class, 'deleteProject']);

    // task routes
    Route::get('/projects/{project}/tasks', [TaskController::class, 'getProjectTasks']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'addTaskToProject']);
    Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'getProjectTask']);
    Route::patch('/projects/{project}/tasks/{task}', [TaskController::class, 'updateProjectTask']);
    Route::patch('/projects/{project}/tasks/{task}/{action}', [TaskController::class, 'updateProjectTaskStatus']);
    Route::delete('/projects/{project}/tasks/{task}/{type}', [TaskController::class, 'deleteProjectTask']);

    // user routes
    Route::patch('/users/{user}', [UserController::class, 'updateUser']);
});
