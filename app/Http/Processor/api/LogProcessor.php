<?php

namespace App\Http\Processor\api;
use App\Http\Services\api\ApiService;
use DB;

class LogProcessor{
    
    public $ApiService;
    public function __construct(){
        $this->ApiService = new ApiService();
    }

    //InitCloudWatchRequestLog
    public function InitCloudWatchRequestLog($request,$response){
        //initializing a log request
        $logRequest = [];

        if($request['method_name'] == 'POST' || $request['method_name'] == 'PUT'){
            if($request['body'] != null){
                $requestBody = $request['body'];
            }else{
                $requestBody = null;
            }
        }else{
            $requestBody = null;
        }

        //log exchange api request response log
        $loggingID  = "log_".strtotime(date('Y-m-d H:i:s'));
        $status     = $response->status();

        if(config('constant.LOG_REQUIRED') == true){
            
            if($request['request_name'] == "IPOMasterApi" || $request['request_name'] == "SGBMasterApi"
            || $request['request_name'] == "NCBMasterApi"){
            
            $logRequest = [
                'log_id'                => $loggingID,
                'error_message'         => ($response->failed()) ? $this->ApiService->GetErrorFromApiResponse($response->json()) : null,
                'request_name'          => $request['request_name'],
                'request_url'           => $request['url'],
                'request_method'        => $request['method_name'],
                'request_header'        => $request['headers'],
                'request_message'       => $requestBody,
                'response_message'      => $response->json() ? config('constant.RESPONSE_MESSAGE') : 'false',
                'response_status_code'  => $status,
                // 'response_status'      => Lang::get("response.$status"),
                'request_time'          => $request['request_time'],
                'response_time'         => date('Y-m-d H:i:s'),
                'tat_time'              => $this->ApiService->GetTimeDifferenceInSeconds($request['request_time'],date('Y-m-d H:i:s')),
                ];
            }else{
                $logRequest = [
                    'log_id'                => $loggingID,
                    'error_message'         => ($response->failed()) ? $this->ApiService->GetErrorFromApiResponse($response->json()) : null,
                    'request_name'          => $request['request_name'],
                    'request_url'           => $request['url'],
                    'request_method'        => $request['method_name'],
                    'request_header'        => $request['headers'],
                    'request_message'       => $requestBody,
                    'response_message'      => $response->json(),
                    'response_status_code'  => $status,
                    // 'response_status'      => Lang::get("response.$status"),
                    'request_time'          => $request['request_time'],
                    'response_time'         => date('Y-m-d H:i:s'),
                    'tat_time'              => $this->ApiService->GetTimeDifferenceInSeconds($request['request_time'],date('Y-m-d H:i:s')),
                ];
            }
        }

        //create a log stream
        \Log::channel('ipo-logs')->info($loggingID, $logRequest);
        // \Log::channel('response')->info($loggingID, $logRequest);

        if(config('constant.DB_LOG_REQUIRED') == true){
            $this->InitDBRequestResponseLogProcessor($request,$response,$status);
        }

        //send response
        return $loggingID;
    }

    function InitDBRequestResponseLogProcessor($request,$response,$statusCode) {
        try {
            $Logbody        = $request['Logbody'];
            $exchange_type  = isset($Logbody['exchange_type'])  ?  $Logbody['exchange_type'] : null;
            $ucc_no         = isset($Logbody['ucc_no'])         ?  $Logbody['ucc_no'] : null;
            $IPOAppId       = isset($Logbody['IPOAppId'])       ?  $Logbody['IPOAppId'] : null;
            $symbol         = isset($Logbody['symbol'])         ?  $Logbody['symbol'] : null;
            $requestBody    = $request['body']                  ?  $request['body'] : null;
            $IPReq          = \Request::ip();
            $tat_time       = $this->ApiService->GetTimeDifferenceInSeconds($request['request_time'],date('Y-m-d H:i:s'));

            $errorMsg       = null;
            $status         = null;
            if($response->failed() && $statusCode > 200){
                // $errorMsg = "Error in API while processing request";
                $errorMsg = ($response->failed()) ? $this->ApiService->GetErrorFromApiResponse($response->json()) : null;
            }else{
                $status = "Success";
            }

            $apiLogId=DB::select("call sp_insert_ipo_api_logs(
                '".$ucc_no."',
                '".$IPOAppId."',
                '".$symbol."',
                '".$exchange_type."',
                '".$request['api_name']."',
                '".$request['url']."',
                '".$IPReq."',
                '".$request['headers']."',
                '".json_encode($requestBody)."',
                '".$errorMsg."',
                '".$response."',
                '".$status."',
                '".$statusCode."',
                '".$request['request_time']."',
                '".date('Y-m-d H:i:s')."',
                '".$request['request_time']."',
                '".'API'."',
                '".'24'."'
            )");

            // \Log::channel('response')->info(["DB Status"=> $apiLogId]);
        } catch(\Exception $e) {
            \Log::channel('response')->info(["DB Error Status"=> $e->getMessage(), "Error Msg"=>$e->getLine()]);
        }
    }

}
