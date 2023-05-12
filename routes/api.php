<?php

use App\Http\Controllers\AuthController;
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
], function ($router) {
    Route::get('/projects', function (\Illuminate\Http\Request $request) {
        dd($request->bearerToken());
    });
});
