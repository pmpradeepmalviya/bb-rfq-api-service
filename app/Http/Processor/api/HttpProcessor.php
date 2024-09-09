<?php

namespace App\Http\Processor\api;
use Illuminate\Support\Facades\Http;
use App\Http\Services\api\ApiService;
use App\Http\Processor\api\LogProcessor;

class HttpProcessor {

    public $ApiService,$LogProcessor;
    public function __construct(){
        $this->ApiService = new ApiService();
        $this->LogProcessor = new LogProcessor();
    }

    public function InitHttpPostRequestProcessor($request){
        // INITIALIZE A RESPONSE
        $response = [
            'error_code' => null,
            'data' => null,
        ];
        // INITIALIZE A HTTP CLIENT
        $response = Http::withHeaders($request['headers'])->post($request['url'],$request['body']);
       // dd($response);
        // INITIALIZE A RESPONSE LOGGER TO SAVE ALL RESPONSE INTO AMAZON CLOUD WATCH
            
        //$loggingID = $this->LogProcessor->InitCloudWatchRequestLog($request,$response);
        // CHECK HTTP RESPONSE
        if($response->failed()){
            // HANDLING SERVER ERRORS
            $jsonResponse = $response->json();
            $status_code = $response->status();
            // $error_response = $this->ApiService->SendServerErrorResponse($status_code);
            $response = [
                'error_code'    => $this->ApiService->GetErrorFromApiResponse($jsonResponse),
                'is_success'    => false,
                'response'      => $jsonResponse,
            ];
            return $response;
        }
        // GENERATE A RESPONSE
        // dd($response->json());
        $response = [
            'error_code'        => null,
            'is_success'        => true,
            //'request_log_id'    => $loggingID,
            'data'              => $response->json(),
        ];
        // dd($response);
        return $response;
    }

    public function InitHttpGetRequestProcessor($request,$shilpiflag=null){
        // INITIALIZE A LOG REQUEST
        $loggingID = null;
        // INITIALIZE A RESPONSE
        $response = [
            'error_code' => null,
            'data' => null,
        ];
        // INITIALIZE A HTTP CLIENT
        $response = Http::withHeaders($request['headers'])->get($request['url']);

        // CHECK HTTP RESPONSE
        if($response->failed()){
            // HANDLING SERVER ERRORS
            $jsonResponse = $response->json();
            $status_code = $response->status();
            $error_response = $this->ApiService->SendServerErrorResponse($status_code);
            $response = [
                'error_code'    => $this->ApiService->GetErrorFromApiResponse($jsonResponse),
                'is_success'    => false,
                'response'      => $error_response,
            ];
            return $response;
        }
        if($shilpiflag==true){
           return $response = [
                'error_code'        => null,
                'is_success'        => true,
                'request_log_id'    => $loggingID,
                'data'              => $response->getBody()->getContents(),
            ];
        }
        // GENERATE A RESPONSE
        $response = [
            'error_code'        => null,
            'is_success'        => true,
            'request_log_id'    => $loggingID,
            'data'              => $response->json(),
        ];
        return $response;
    }

    public function makeCurlRequest($postData, $callApiUrl, $method = 'POST'){
        // dd($postData);
       $curlHandle = curl_init();
       curl_setopt($curlHandle, CURLOPT_HEADER, 0);
       curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $postData['headers']);
       curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
       curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curlHandle, CURLOPT_TIMEOUT, 0);
       curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
       curl_setopt($curlHandle, CURLOPT_URL, $callApiUrl);
       if($method === 'POST'){
           curl_setopt($curlHandle, CURLOPT_POST, 1);
       }else{
           curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
       }
       curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postData['params']);
       $response = curl_exec($curlHandle);
       // dd( $response);
       $info = curl_getinfo($curlHandle);
       $err = curl_error($curlHandle);
       curl_close($curlHandle);
       return compact('response', 'info', 'err');
    }

    public function makeCurlRequestPayments($postData){
        $headers = [
            'Content-Type:application/json',
            'Authorization: Bearer '.$postData['token'],
        ];

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_URL, $postData['url']);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postData['params']);
        $response = curl_exec($curlHandle);
      
        $info = curl_getinfo($curlHandle,CURLINFO_HTTP_CODE);
        $err = curl_error($curlHandle);
       // dd($info);
        curl_close($curlHandle);
        return $response;
    }

    public function InitHttpPostRequestProcessorPayments($request){
        // INITIALIZE A RESPONSE
        $response = [
            'error_code' => null,
            'data' => null,
        ];
        $response = Http::asForm()->post($request['url'], $request['body']);
      
        // CHECK HTTP RESPONSE
        if($response->failed()){
            // HANDLING SERVER ERRORS
            $jsonResponse = $response->json();
            $status_code = $response->status();
            // $error_response = $this->ApiService->SendServerErrorResponse($status_code);
            $response = [
                'error_code'    => $this->ApiService->GetErrorFromApiResponse($jsonResponse),
                'is_success'    => false,
                'response'      => $jsonResponse,
            ];
            return $response;
        }
        // GENERATE A RESPONSE
        $response = [
            'error_code'        => null,
            'is_success'        => true,
            'data'              => $response->json(),
        ];
        return $response;
    }

    public function makeCurlRequestCheckSum($header,$postData){
        $url = config('constant.BSEValidateCheckSum');
        $headers = $header ;
       //dd($postData);
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_URL,$url);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($curlHandle);
        $info = curl_getinfo($curlHandle,CURLINFO_HTTP_CODE);
        $err = curl_error($curlHandle);
       // dd($info);
        curl_close($curlHandle);
        return json_decode($response);
    }

}


