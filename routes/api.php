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

//Route::group([
//    'middleware' => 'api',
//    'prefix' => 'auth'
//], function ($router) {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt')->get('/projects', function (\Illuminate\Http\Request $request) {
        dd($request->bearerToken());
    });

//});