<?php
namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Validation;
use Illuminate\Support\Facades\Http;
use App\Http\Services\api\ApiService;
use Illuminate\Http\Response;
use App\Http\Request\api\GenerateApiRequestResponseBody;
use App\Http\Processor\api\SpProcessor;
use Exception;

class ReportingController extends BaseController
{
    public function __construct(){
        parent::__construct();
        $this->GenerateApiRequestResponseBody = new GenerateApiRequestResponseBody();
        $this->SpProcessor = new SpProcessor();
    }

    //ORDER REPORT DETAILS
    public function RFQOrderReportData(Request $request){
        
        $error = $this->ValidationModel->ValidateOrderReport($request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $orderReportBody = $this->GenerateApiRequestResponseBody->CreateOrderReportBody($request);
        $getOrderData = $this->SpProcessor->getOrderReportData($orderReportBody);
       
        return $getOrderData;
    }

    //TRADE REPORT DETAILS
    public function RFQTradeReportData(Request $request){
        
        $error = $this->ValidationModel->ValidateTradeReport($request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $tradeReportBody = $this->GenerateApiRequestResponseBody->CreateTradeReportBody($request);
        $getTradeData = $this->SpProcessor->getTradeReportData($tradeReportBody);
        return $getTradeData;
    }

    //TRADE HISTORY REPORT DETAILS
    public function RFQTradeHistoryReportData(Request $request){
        $error = $this->ValidationModel->ValidateTradeHistoryReport($request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $tradeHistoryReportBody = $this->GenerateApiRequestResponseBody->CreateTradeReportBody($request);
        $gettradehistoryData = $this->SpProcessor->getTradeReportData($tradeHistoryReportBody);
        return $gettradehistoryData;
    }

    //ORDER REPORT PROP UPDATE
    public function RFQOrderReportPropUpdate(Request $request){
        
        $error = $this->ValidationModel->ValidateOrderReport($request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $orderReportBody = $this->GenerateApiRequestResponseBody->CreateOrderReportPropUpdateBody($request);
        
        $getOrderData = $this->SpProcessor->orderReportPropUpdate($orderReportBody);
        
        return $getOrderData;
    }

    // DELETE ORDER REPORT
    public function RFQDeleteOrderReport(Request $request){
        
        $error = $this->ValidationModel->ValidateOrderReport($request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $orderReportBody = $this->GenerateApiRequestResponseBody->CreateDeleteOrderReportBody($request);
        
        $getOrderData = $this->SpProcessor->deleteOrderReport($orderReportBody);
        
        return $getOrderData;
    }
}
