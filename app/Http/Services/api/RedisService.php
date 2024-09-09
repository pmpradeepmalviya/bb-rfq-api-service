<?php

namespace App\Http\Services\api;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisService extends BaseService{

    //SetupARedisDetails
    public function SetupRedisDetails($request){

        $keyName = "rfq_redis_details";

        //encode request data into json format
        $encodedData = json_encode($request,true);

        //setting up redis details
        Redis::set($keyName,$encodedData);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['keyName'=>$keyName,'data'=>$encodedData]);
        }
        //send response
        return true;
    }

    //GetRedisDetails
    public function GetRedisDetails(){

        //initialize a response array
        $responseArr = [];

        //generating a keyname
        $keyName = "rfq_redis_details";

        //check redis -key exist or not
        if(Redis::exists($keyName)){
            $encodedRequest = Redis::get($keyName);
            $decodedRequest = json_decode($encodedRequest,true);
            $responseArr = $decodedRequest;
            $responseArr['error_code'] = null;
            if(config('constant.FILE_LOG_REQUIRED')==true){
             Log::channel("response")->info(['keyexistresp'=>$responseArr,'data'=>$encodedRequest]);
            }
        }else{
            $responseArr = [
                'error_code' => 1010
            ];   
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['keydosentexistresp'=>$responseArr]);
            }
        }

        //send response
        return $responseArr;
    }

    //DeleteFromRedisDetails
    public function DeleteFromRedisDetails(){
        
        //generating a keyname
        $keyName = "rfq_redis_details";

        if(Redis::exists($keyName)){
            Redis::del($keyName);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['deletkey'=>$keyName]);
            }
        }
        return true;
    }

    /////////////////////////////////////payments////////////////////////////////////////
    public function SetupPaymentRedisDetails($request){

        $keyName = "rfq_payment_redis_details";

        //encode request data into json format
        $encodedData = json_encode($request,true);

        //setting up redis details
        Redis::set($keyName,$encodedData);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['keyName'=>$keyName,'data'=>$encodedData]);
        }
        //send response
        return true;
    }

    //GetRedisDetails
    public function GetPaymentRedisDetails(){

        //initialize a response array
        $responseArr = [];

        //generating a keyname
        $keyName = "rfq_payment_redis_details";

        //check redis -key exist or not
        if(Redis::exists($keyName)){
            $encodedRequest = Redis::get($keyName);
            $decodedRequest = json_decode($encodedRequest,true);
            $responseArr = $decodedRequest;
            $responseArr['error_code'] = null;
            if(config('constant.FILE_LOG_REQUIRED')==true){
             Log::channel("response")->info(['paymentkeyexistresp'=>$responseArr,'paymentdata'=>$encodedRequest]);
            }
        }else{
            $responseArr = [
                'error_code' => 1010
            ];   
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['paymentkeydosentexistresp'=>$responseArr]);
            }
        }

        //send response
        return $responseArr;
    }

    //DeleteFromRedisDetails
    public function DeletePaymentRedisDetails(){
        
        //generating a keyname
        $keyName = "rfq_payment_redis_details";

        if(Redis::exists($keyName)){
            Redis::del($keyName);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['deletpaymentkey'=>$keyName]);
            }
        }
        return true;
    }
}
