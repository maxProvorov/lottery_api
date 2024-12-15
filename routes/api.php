<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstatePoolController;

Route::post('realestate_pewpool', [EstatePoolController::class, 'createPool']);
Route::post('realestate_addgift', [EstatePoolController::class, 'addGift']);
Route::post('realestate_buytickets', [EstatePoolController::class, 'buyTickets']);
Route::post('realestate_checkgift', [EstatePoolController::class, 'checkGift']);
Route::get('realestate_inform', [EstatePoolController::class, 'getInfo']);
Route::get('realestate_mytickets', [EstatePoolController::class, 'getMyTickets']);
Route::get('realestate_poolinfo', [EstatePoolController::class, 'getPoolInfo']);
Route::get('/realestate_myticketsinfo', [EstatePoolController::class, 'getMyTicketsInfo']);
