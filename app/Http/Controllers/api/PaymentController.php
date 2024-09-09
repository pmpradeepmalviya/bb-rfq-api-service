<?php

namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use App\Http\Services\api\RedisService;
use App\Http\Services\api\AwsService;
use Aws\SecretsManager\SecretsManagerClient;
use App\Http\Processor\api\SpProcessor;
use Illuminate\Support\Facades\Log;
use App\Http\Processor\api\HttpProcessor;
use DateTime;

class PaymentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->RedisService = new RedisService();
        $this->AwsService = new AwsService();
        $this->SpProcessor = new SpProcessor();
    }
    /////////////////payments/////////////////////////

    public function ProcessPayments(Request $paymentReq)
    {
        //dd($paymentReq->All());
        $error = $this->ValidationModel->ValidatePaymentRequest($paymentReq);
        if ($error != null) {
            return $this->ApiService->SendHTTPErroringResponse(422, $error);
        }
        //check token
        $response = $this->checkPaymentToken($paymentReq);
        if ($response["error_code"] != null) {
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info([
                    "checkPaymentToken" => $response,
                ]);
            }
            return $response;
        }
        $paymentLink="";
        $RfqQuoteNo="";
        $jsonbody=json_encode(["TradeDate"=>$paymentReq["trade_date"],"OrderNo"=>$paymentReq["order_no"]]);
        $encryptedBody=$this->encryptRequest(str_replace('"',"'",$jsonbody));

        if(empty($encryptedBody)){
            return $this->ApiService->SendHTTPErrorResponse(423,config('error.1020'));
        }else{
            $paymentBody = $this->GenerateApiRequestResponseBody->ReturnPaymentRequestBody($encryptedBody,$response['token']);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['paymentrequest'=>$paymentBody]);
            }
            $req_time=date('Y-m-d H:i:s');
            // MAKE THE API CALL
            // makeCurlRequest($postData, $callApiUrl, $method = 'POST'){
            $result = $this->HttpProcessor->makeCurlRequestPayments($paymentBody);
            $api_response=json_decode($result,true);
            $res_time=date('Y-m-d H:i:s');
           // dd($api_response);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['paymentrequest link generation res success'=>$api_response]);
            }
            if(isset($api_response["success"]) && $api_response["success"]==true){
                $paymentLink=$api_response["paymentLink"];
                $RfqQuoteNo=$api_response["orderNo"];
            }else{
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['Payment API call failed res'=>$api_response]);
                }
            }

            //Save Logs
            $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($paymentLink,$paymentBody,$api_response,"https://uat-rfqepay.bseindia.com/api/SendPaymentLink","PaymentLinkGeneration",$paymentReq['trade_no'],$req_time,$res_time,$paymentReq['client_ip']);
            $loginsertion=$this->SpProcessor->InsertAPILogs($logs);
            Log::channel("query")->info(['LogsInsertion'=>$loginsertion]);
             
            // hit the api again in case of auth failed - we rcv this when token expireswhile hitting payments
            /*sample response - {
                "message": "Authorization has been denied for this request."
            }*/
            if(isset($api_response["message"]) && $api_response["message"]=="Authorization has been denied for this request."){
                $response = $this->handleTokenExpire($paymentReq);
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['paymentshandletokenexpirecase'=>$response]);
                }
                if ($response["error_code"] != null) {
                    if (config("constant.FILE_LOG_REQUIRED") == true) {
                        Log::channel("response")->info([
                            "payment token err handle case" => $response,
                        ]);
                    }
                    return $response;
                }else{
                    $req_time=date('Y-m-d H:i:s');
                    //got the token again so now hit the payment link again 
                    $paymentBody = $this->GenerateApiRequestResponseBody->ReturnPaymentRequestBody($encryptedBody,$response['token']);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['paymentrequest'=>$paymentBody]);
                    }
                    // MAKE THE API CALL
                    $result = $this->HttpProcessor->makeCurlRequestPayments($paymentBody);
                    $api_response=json_decode($result,true);
                    $res_time=date('Y-m-d H:i:s');
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['paymentrequesttoken_expire_case'=>$paymentBody,"paymentresphandlecase"=>$api_response]);
                    } 
                    if(isset($api_response["success"]) && $api_response["success"]==true){
                        $paymentLink=$api_response["payment_link"];
                        $RfqQuoteNo=$api_response["orderNo"];
                    }else{
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['Payment API call failed res'=>$api_response]);
                        }
                        //Save Logs
                        $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($paymentLink,$paymentBody,$api_response,"https://uat-rfqepay.bseindia.com/api/SendPaymentLink","PaymentLinkGenerationHandlecase",$paymentReq['trade_no'],$req_time,$res_time,$paymentReq['client_ip']);
                        $loginsertion=$this->SpProcessor->InsertAPILogs($logs);
                        Log::channel("query")->info(['PaymentSuccessCaseLogsInsertion'=>$loginsertion]);
                    }

                    $CreateTxnReqBody=$this->GenerateApiRequestResponseBody->CreateTxnReqBody($paymentReq,$paymentLink);
                    $InsertIntoTxnTableRes=$this->SpProcessor->InsertIntoTxnTable($CreateTxnReqBody);
                 
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("query")->info(['InsertIntoTxnTableResHandlecase sp res'=>$InsertIntoTxnTableRes]);
                    }
                    if($InsertIntoTxnTableRes[0]->ret_status !=0 ){
                        return $this->ApiService->SendHTTPErrorResponse(423,config('error.1018'));
                    }
                    if (isset($api_response)){
                        $link=($paymentLink!="")?$paymentLink:NULL;
                        $RfqQuoteNo=($RfqQuoteNo!="")?$api_response["orderNo"]:0;
                        return ["Link"=>$link,"RfqQuoteNo"=>$RfqQuoteNo];
                    }else{
                        return ["Link"=>"","RfqQuoteNo"=>$RfqQuoteNo];
                    }
                }
            }
        }
       //Now need to do payment table insertion 
        $CreateTxnReqBody=$this->GenerateApiRequestResponseBody->CreateTxnReqBody($paymentReq,$paymentLink);
        $InsertIntoTxnTableRes=$this->SpProcessor->InsertIntoTxnTable($CreateTxnReqBody);
 
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['InsertIntoTxnTableRes sp res'=>$InsertIntoTxnTableRes]);
        }
        if($InsertIntoTxnTableRes[0]->ret_status !=0 ){
            return $this->ApiService->SendHTTPErrorResponse(423,config('error.1018'));
        }
        if (isset($api_response)){
            $link=($paymentLink!="")?$paymentLink:NULL;//$api_response["error_Mesg"];
            $RfqQuoteNo=($RfqQuoteNo!="")?$api_response["orderNo"]:0;
            return ["Link"=>$link,"RfqQuoteNo"=>$RfqQuoteNo];
        }else{
            return ["Link"=>"","RfqQuoteNo"=>$RfqQuoteNo];
        }
 
    }
    public function handleTokenExpire($paymentReq) {

        $Delete_redis_flag = $this->RedisService->DeletePaymentRedisDetails();

        $response = $this->GenerateTokenApiFunc($paymentReq);
        if($response['error_code'] != null){
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['Paymentsexpireredistokenloginfailed'=>$response]);
            }
            return $response;
        }
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Paymentsexpireredistokenloginsuccess'=>$response]);
        }
        return $response;
       
        
    }
    public function encryptRequest($stringToEncrypt){
            $Body = $this->GenerateApiRequestResponseBody->ReturnEncApiBody($stringToEncrypt);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['encryption request'=>$Body]);
            }
            // MAKE THE API CALL
            $api_response = $this->HttpProcessor->InitHttpPostRequestProcessor($Body);
            Log::channel("response")->info(['Encryption API call res'=>$api_response]);

           //dd("apiresponse",$api_response["data"]["data"]);
            if(isset($api_response["data"]["success"]) && $api_response["data"]["success"]==true){
                $encData=$api_response["data"]["data"];
            }else{
                dd("ithe nako");
                $encData="";
                if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['Encryption API call failed res'=>$api_response]);
            }
        }
        return $encData;

    }
    
    public function checkPaymentToken($paymentReq)
    {
        // CHECK FOR LOGIN TOKEN
        $redis_data = $this->RedisService->GetPaymentRedisDetails();
        if (config("constant.FILE_LOG_REQUIRED") == true) {
            Log::channel("response")->info(["paymentgetredis" => $redis_data]);
        }
        if (empty($redis_data["token"]) || $redis_data["error_code"] != null) {
            $PaymentResp = $this->GenerateTokenApiFunc($paymentReq);
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info(["GenerateTokenApiFunc PaymentResp" => $PaymentResp]);
            }
             // success , hardcoding response as token is already given 13/02 so i was getting error
            // $PaymentResp=[
            //     "error_code" => null,
            //     "is_success" => true,
            //     "data"=> [
            //     "access_token" => "RsoIVtHlgVHCsyvk51rDb5IuCWGVk72sz9gCzLm-rwvyQtqDzqWUfp2QWzgsiJ5OPEP0NtSOsQJ5SMKfedZWB7JIdQNo34L0Pu0b5MG2HjHHTVSq_0arN2ULikoWqdPTamLA_80CsxklYAhr-gZsL_4auGVl3yg5MGgGT8OYxv7Z1jwWxP8bT8LLfGSN_hqJ9qCA5lxSdhk3vI9lmj8kcB6zAbRkaVPJ39RP5OX7n4xx7RofnxhwFCxteh4RfSvS1gz7v5YxhX8bzBFKO7lx_LmtaEOd3awGih0REnfnbOLRQbSb19ZLL1UDH9v-8mww", 
            //     "token_type" => "bearer", 
            //     "expires_in" => 86399 
            //    ],
            // ];
           
            if ($PaymentResp["error_code"] != null) {
                if (config("constant.FILE_LOG_REQUIRED") == true) {
                    Log::channel("response")->info([
                        "paymenterrchkresp" => $PaymentResp,
                    ]);
                }
                return $PaymentResp;
            }
            $response = [
                "error_code" => null,
                "token" => isset($PaymentResp["data"]["access_token"])?$PaymentResp["data"]["access_token"]:null,
                "status" => "success",
            ];
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info([
                    "successchkpaymentresp" => $response,
                ]);
            }
            return $response;
        } else {
            $response = [
                "error_code" => null,
                "token" => $redis_data["token"],
                "status" => "success",
            ];
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info([
                    "directredisfetchofpayment" => $response,
                ]);
            }
            return $response;
        }
    }

    public function GenerateTokenApiFunc($paymentReq)
    {
        // LOGIN DATA
        try {
            $req_time=date('Y-m-d H:i:s');
            $paymentsDataResp = $this->AwsService->getSecret();
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info([
                    "paymentsDataResp SecretsManager" => $paymentsDataResp,
                ]);
            }
            $res_time=date('Y-m-d H:i:s');
            if ($paymentsDataResp["error_code"] != null) {
                $logs = $this->GenerateApiRequestResponseBody->CreateLogsBody(
                    "failed",
                    "",
                    $paymentsDataResp["data"],
                    $paymentsDataResp["data"]["pay_api_url"],
                    "PaymentsSecretManagerCall",
                    $paymentReq['trade_no'],
                    $req_time,
                    $res_time,
                    $paymentReq['client_ip']
                );
                $loginsertion = $this->SpProcessor->InsertAPILogs($logs);
                return $this->ApiService->SendHTTPErroringResponse(
                    $paymentsDataResp["error_code"],
                    "failed"
                );
            }

            $ReqBody = $this->GenerateApiRequestResponseBody->CreatePaymentTokenReqBody(
                $paymentsDataResp["data"],
            );
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info(["paymentBody" => $ReqBody]);
            }

            // INITIALIZE A PROCESSOR CALL TO SEND AN HTTP CALL
            $response = $this->HttpProcessor->InitHttpPostRequestProcessorPayments(
                $ReqBody
            );

            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info([
                    "paymentgeneratetokenapiresponse" => $response,
                ]);
            }
           if ($response["is_success"] == false) {
                if (config("constant.FILE_LOG_REQUIRED") == true) {
                    Log::channel("response")->info([
                        "paymentgeneratetokenfailed" => $response,
                    ]);
                }
                $logs = $this->GenerateApiRequestResponseBody->CreateLogsBody(
                    isset($response["access_token"])?"success":"failure",
                    $ReqBody,
                    isset($response["response"]["error_description"])?$response["response"]["error_description"]:null,
                    $paymentsDataResp["data"]["pay_api_url"] . "token",
                    "PaymentsGenerateTokenCall",
                    $paymentReq['trade_no'],
                    $req_time,
                    $res_time,
                    $paymentReq['client_ip']
                );
                $loginsertion = $this->SpProcessor->InsertAPILogs($logs);
                return $response;
            }

            if (
                !empty($response["data"]) && isset($response["data"]["access_token"])) {

                //Save Logs
                $logs = $this->GenerateApiRequestResponseBody->CreateLogsBody(
                    $response["data"]["access_token"],
                    $ReqBody,
                    $response["data"],
                    $paymentsDataResp["data"]["pay_api_url"] . "token",
                    "PaymentsGenerateTokenCall",
                    $paymentReq['trade_no'],
                    $req_time,
                    $res_time,
                    $paymentReq['client_ip']
                );
                $loginsertion = $this->SpProcessor->InsertAPILogs($logs);

                // RESPONSE AFTER LOGIN API CALL
                $redisData = ["token" =>$response["data"]["access_token"]];

                $this->RedisService->SetupPaymentRedisDetails($redisData);

                $Apiresponse = [
                    "error_code" => null,
                    "token" => $response["data"]["access_token"],
                ];
                if (config("constant.FILE_LOG_REQUIRED") == true) {
                    Log::channel("response")->info([
                        "Paymentsuccesscase" => $Apiresponse,
                    ]);
                }
                return $Apiresponse;
            } else {
                if (config("constant.FILE_LOG_REQUIRED") == true) {
                    Log::channel("response")->info(["paymenterrorcase" => $response]);
                }
                return $this->ApiService->SendHTTPErrorResponse(421,$response["response"]["error"]);
            }
        } catch (\Exception $e) {
            if (config("constant.FILE_LOG_REQUIRED") == true) {
                Log::channel("response")->info([
                    "exception" => $e->getMessage(),
                ]);
            }
            return $this->ApiService->SendHTTPErrorResponse(
                422,
                $e->getMessage()
            );
        }
    }
}
?>
