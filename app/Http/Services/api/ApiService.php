<?php
namespace App\Http\Services\api;
use Exception;
use Illuminate\Support\Facades\Lang;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;

class ApiService extends BaseService{

    // SENDHTTPRESPONSE
    private function SendHTTPResponse($isSuccess,$code,$error=null,$data,$logId=null){
        // SEND BACK A JSON RESPONSE
        return response([
            'status'=>$isSuccess,
            'code'=> $code,
            'error' => $error,
            "data" => $data,
        ],$code)->header('Content-Type','application/json');
    }

    // SENDHTTPERRORRESPONSE
    public function SendHTTPErrorResponse($errorcode,$errormsg){
    
        $error_response = $this->SendHTTPResponse('failed',$errorcode,$errormsg,null,null);
        $response = json_decode($error_response->getContent(), true);
        return $response;
    }

    //SENDHTTPERRORINGRESPONSE
    public function SendHTTPErroringResponse($responseCode,$error){
        return $this->SendHTTPResponse('failed',$responseCode,$error,null,null);
    }

    // // SENDHTTPERRORRESPONSE
    public function SendServerErrorResponse($errorCode){
        $error_response = $this->SendHTTPResponse('failed',$errorCode,null,null,null);
        $response = json_decode($error_response->getContent(), true);
        return $response;
    }

    // GETERRORFROMAPIRESPONSE
    public function GetErrorFromApiResponse($response){
        // DEFINE A MANUAL ERROR
        $error = Lang::get('errors.1011');
        // CHECK IS THERE ANY ERROR PRESENT IN THE RESPONSE
        if($response != null && array_key_exists('result',$response) && $response['result'] != null){
            if(array_key_exists('errors',$response['result'])){
                if(array_key_exists('messages',$response['result']['errors'][0])){
                    if(is_array($response['result']['errors'][0]['messages'])){
                        return $response['result']['errors'][0]['messages'][0];
                    }else{
                        return $response['result']['errors'][0]['messages'];
                    }
                }
            }
        }else if($response != null && array_key_exists('description',$response)){
            return $response['description'];
        }else if($response != null && array_key_exists('Errors',$response)){
            return $response['Errors'][0];
        }else{
            return $error;
        }
        // SEND RESPONSE
        return $error;
    }

    // GETTIMEDIFFERENCEINSECONDS
    public function GetTimeDifferenceInSeconds($startTime,$endTime){
        return strtotime($endTime) - strtotime($startTime);
    }

    //ExcludeArrayFields
    public function ExcludeArrayFields($secrtmanagerdata,$fields){
        $response = $request;
        foreach ($fields as $FieldName) {
            if(array_key_exists($FieldName,$response)){
                unset($response[$FieldName]);
            }
        }
        //send response
        return $response;
    }

    //SendHTTPSuccessResponse
    public function SendHTTPSuccessResponse($response){
       
        $jsonResponse = [];
        // $response = $this->ExcludeArrayFields($response,['auth_token','error_code','request_log_id']);
        if(array_key_exists('data',$response)){
            $jsonResponse = $response['data'];
            if(array_key_exists('data',$jsonResponse)){
                $jsonResponse = $jsonResponse['data'];
            }
        }else{
            $jsonResponse = $response;
        }
        $isSuccess = "success";
        //send back a JSON error response
        return $this->SendHTTPResponse($isSuccess,200,null,$jsonResponse);
    }

    public function ReplaceArray($input,$keyStream1,$keyStream2){
        return str_replace($keyStream1,$keyStream2,$input);
    }

    public function GenerateStandardHTTPHeader($type,$secrtmanagerdata,$token){
        $headers= [
            "MEMBERCODE" =>trim($secrtmanagerdata['data']['membercode']),
            "USERID" =>trim($secrtmanagerdata['data']['loginid']),
            "PASSWORD" =>trim($secrtmanagerdata['data']['password']),
        ];
        if ($type!="Login"){
            $headers= [
                "Content-Type" => "application/json",
                "MEMBERCODE" =>trim($secrtmanagerdata['data']['membercode']),
                "USERID" =>trim($secrtmanagerdata['data']['loginid']),
                "PASSWORD" =>trim($secrtmanagerdata['data']['password']),
                "TOKEN" =>trim($token)
            ];

        }

        return $headers;
    }

    public function GenerateCheckSumHTTPHeader($endpoint,$secrtmanagerdata,$token){
        $headers= [
           
            "MEMBERCODE: ".trim($secrtmanagerdata['data']['membercode']),
            "USERID: ".trim($secrtmanagerdata['data']['loginid']),
            //"PASSWORD" =>trim($secrtmanagerdata['data']['password']),
            "TOKEN: ".trim($token),
            "ENDPOINT: ".$endpoint,
            "Content-Type: text/plain",
        ];
        return $headers;
    }
   
}
