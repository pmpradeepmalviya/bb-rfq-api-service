<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\APIController;
use App\Http\Controllers\api\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/exportpdf',[APIController::class,'GenerateDealSlip']);
Route::get('/testapi',[UserController::class,'TestAPI']);
