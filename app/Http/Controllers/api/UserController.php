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
use DateTime;

class UserController extends BaseController{

    public function __construct(){
        parent::__construct();
        $this->RedisService = new RedisService();
        $this->AwsService = new AwsService();
        $this->SpProcessor = new SpProcessor();
        
    }

    // LOGIN API FUNCTIONALITY
    public function LoginApiFunc($client_ip){
        // LOGIN DATA
        try{
           $req_time=date('Y-m-d H:i:s');
           $secretDataResp = $this->AwsService->getSecret();
           $res_time=date('Y-m-d H:i:s');
           if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['SecretManagerData USercontroller'=>$secretDataResp]);
            }
            if($secretDataResp['error_code'] != null){
                $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody("failed",'',$secretDataResp['data'],$secretDataResp['data']['api_url'],"SecretManagerCall",0,$req_time,$res_time,$client_ip);
                $loginsertion=$this->SpProcessor->InsertAPILogs($logs);
                return $this->ApiService->SendHTTPErroringResponse($secretDataResp['error_code'],"failed");  
            }
               
            //Generate standard HTTP Headers
            $header = $this->ApiService->GenerateStandardHTTPHeader("Login",$secretDataResp,null);
            $ReqBody = $this->GenerateApiRequestResponseBody->CreateloginReqBody($secretDataResp['data'],$header);
            $req_time=date('Y-m-d H:i:s');
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['loginbody'=>$ReqBody]);
            }

            // INITIALIZE A PROCESSOR CALL TO SEND AN HTTP CALL
            $response = $this->HttpProcessor->InitHttpGetRequestProcessor($ReqBody);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['loginapiresponse'=>$response]);
            }
            $res_time=date('Y-m-d H:i:s');
            if($response['is_success'] == false){   
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['loginapiresponseFailed'=>$response]);
                }
                $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($response['data']['TokenResponceList'][0]['MESSAGE'],$ReqBody,$response['data'],$secretDataResp['data']['api_url']."GenerateToken","LoginAPICall",0,$req_time,$res_time,$client_ip);
                $loginsertion=$this->SpProcessor->InsertAPILogs($logs);
                return $response;  
            }

            if(!empty($response['data']) && $response['data']['TokenResponceList'][0]['ERRORCODE'] == "0"){
                
                //Save Logs
                $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($response['data']['TokenResponceList'][0]['MESSAGE'],$ReqBody,$response['data'],$secretDataResp['data']['api_url']."GenerateToken","LoginAPICall",0,$req_time,$res_time,$client_ip);
                $loginsertion=$this->SpProcessor->InsertAPILogs($logs);
                
                // RESPONSE AFTER LOGIN API CALL
                $redisData = [
                    'token'         => $response['data']['TokenResponceList'][0]['TOKEN'],
                    'flag'          => 'Active'
                    ];
                    
                $this->RedisService->SetupRedisDetails($redisData);

                $Apiresponse = [
                    'error_code'    => null,
                    'token'         => $response['data']['TokenResponceList'][0]['TOKEN'],
                    'flag'          => 'Active'
                ];
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['successcase'=>$Apiresponse]);
                }
                return $Apiresponse;
            }else{
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['errorcase'=>$response]);
                }
                return $this->ApiService->SendHTTPErrorResponse(421,$response['data']['TokenResponceList'][0]['MESSAGE']);
            }
            
        }catch(\Exception $e){
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['exception'=>$e->getMessage()]);
            }
            return $this->ApiService->SendHTTPErrorResponse(422,$e->getMessage());
        }
        
    }

    // CHANGE PASSWORD API FUNCTIONALITY
    // public function ChangePasswordApiFunc(){
    //     // AWS SECRET MANAGER CODE
    //     $secret_data = $this->AwsService->getSecret();
    //     $new_password = $this->generateRandomPassword();
    //     $data = $this->GenerateApiRequestResponseBody->changepassworddata($secret_data,$new_password);
    //     // VALIDATION
    //     // $error = $this->ValidationModel->ValidateChangePassword($data);
    //     // if($error != null){
    //     //     return $this->ApiService->SendHTTPErroringResponse(422,$error);
    //     // }
    //     // INITIALIZE A PROCESSOR CALL TO SEND AN HTTP CALL
    //     $response = $this->HttpProcessor->InitHttpPostRequestProcessor($data);
    //     if(!empty($response['data']))
    //     {
    //         if($response['data']['status'] == 'success'){
    //             $set_new_password = $this->AwsService->setSecret($new_password,$secret_data);
    //             return $this->LoginApiFunc();
    //         }
    //         else{
    //             $error_code = 409;
    //             return $this->ApiService->SendHTTPErrorResponse(409,$error_code);
    //         }
    //     }
    //     else{
    //         return $response;
    //     }
    // }

    // PASSWORD GENERATION
    public function generateRandomPassword() {
        // Fixed portion of the password
        $fixedPart = 'BBspl@#';
        // Length of the random number part
        $randomNumberLength = 6; // You can adjust this as needed
        // Generate a random number with the specified length
        $randomNumber = str_pad(rand(0, pow(10, $randomNumberLength) - 1), $randomNumberLength, '0', STR_PAD_LEFT);
        // Combine the fixed part and the random number
        $password = $fixedPart . $randomNumber;
        return $password;
    }

    // CHECK LOGIN TOKEN
    public function checkLoginToken($client_ip){
        // CHECK FOR LOGIN TOKEN
        $redis_data = $this->RedisService->GetRedisDetails();
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['getredis'=>$redis_data]);
        }
        if(empty($redis_data['token']) || $redis_data['error_code'] != null){
            $LoginResp = $this->LoginApiFunc($client_ip);
            if($LoginResp['error_code'] != null){
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['errchkloginresp'=>$LoginResp]);
                }
                return $LoginResp;  
            }
            $response = [
                'error_code'    => null,
                'token'         => $LoginResp['token'],
                'status'        => 'success'
            ];
            if(config('constant.FILE_LOG_REQUIRED')==true){
             Log::channel("response")->info(['successchkloginresp'=>$response]);
            }
            return $response;
        }
        else{
            $response = [
                'error_code'    => null,
                'token'         => $redis_data['token'],
                'status'        => 'success'
            ];
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['directredisfetch'=>$response]);
            }
            return $response;
        }
    }

    // CHECK LOGIN TOKEN
    public function setLoginTokenExpire($client_ip){

        $response = [
            'error_code' => null,
        ];

        $Delete_redis_flag = $this->RedisService->DeleteFromRedisDetails();

        $response = $this->LoginApiFunc($client_ip);
        if($response['error_code'] != null){
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['expireredistokenloginfailed'=>$response]);
            }
            return $response;
        }
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['expireredistokenloginsuccess'=>$response]);
        }
        return $response;
    }

    //To Test BSE API connectivity

    public function TestAPI(){
        // LOGIN DATA
        try{
           // dd("hi");
           $req_time=date('Y-m-d H:i:s');
           $secretDataResp = $this->AwsService->getSecret();
           //   dd($secretDataResp);
           $res_time=date('Y-m-d H:i:s');
               
            //Generate standard HTTP Headers
            $header = $this->ApiService->GenerateStandardHTTPHeader("Login",$secretDataResp,null);
            $ReqBody = $this->GenerateApiRequestResponseBody->CreateloginReqBody($secretDataResp['data'],$header);
           
            $req_time=date('Y-m-d H:i:s');
            // INITIALIZE A PROCESSOR CALL TO SEND AN HTTP CALL
            $response = $this->HttpProcessor->InitHttpGetRequestProcessor($ReqBody);
             dd($response);
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
    }
}
