<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\APIController;
use App\Http\Controllers\api\SessionController;
use App\Http\Controllers\api\OrderPlacementController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\PaymentController;
use App\Http\Controllers\api\ReportingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// DEFINE ACCESS-TOKEN MIDDLEWARE
$AccessTokenRoutes = [
    'middleware' => 'accesstoken'
];
// Route::get('/rfqlogin',[UserController::class,'LoginApiFunc']);
// Route::post('/addrfqorder',[APIController::class,'AddRfqOrderFunc']);

Route::post('/validateUser',[APIController::class,'validateUser']);

Route::group($AccessTokenRoutes,function(){
Route::post('/ProcessOrder',[APIController::class,'ProcessOrder']);
Route::post('/AddNewQuoteInitiator',[APIController::class,'AddNewInitiatorQuote']);
// Route::get('/Testing',[APIController::class,'Testing']);
Route::post('/ProcessPayments',[PaymentController::class,'ProcessPayments']);

Route::post('/getorderreport',[ReportingController::class,'RFQOrderReportData']);
Route::post('/gettradereport',[ReportingController::class,'RFQTradeReportData']); 
Route::post('/gettradehistoryreport',[ReportingController::class,'RFQTradeHistoryReportData']);  
Route::any('/admin_send_notification',[APIController::class,'adminEmailSmsTrigger']);
Route::post('/ModifyOrder',[APIController::class,'ModifyOrder']);

Route::post('/orderreportpropupdate',[ReportingController::class,'RFQOrderReportPropUpdate']);  
Route::post('/deleteorderreport',[ReportingController::class,'RFQDeleteOrderReport']);  

});
