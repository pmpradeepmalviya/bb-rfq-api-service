<?php
namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Validation;
use App\Http\Processor\api\HttpProcessor;
use Illuminate\Support\Facades\Http;
use App\Http\Services\api\ApiService;
use App\Http\Services\api\AwsService;
use App\Http\Services\api\RedisService;
use Illuminate\Http\Response;
use App\Http\Controllers\api\UserController;
use App\Http\Request\api\GenerateApiRequestResponseBody;
use Exception;

class OrderPlacementController extends BaseController
{

    public function __construct(){
        parent::__construct();
        $this->RedisService = new RedisService();
        $this->UserController = new UserController();
        $this->GenerateApiRequestResponseBody = new GenerateApiRequestResponseBody();
        $this->HttpProcessor = new HttpProcessor();
    }

    // TRANSACTION ORDER PLACEMENT
    public function TransactionsOrderPlacement(Request $request){

        // VALIDATIONS
        // $error = $this->ValidationModel->ValidateIPOPlaceOrder($request);
        // if($error != null){
        //     return $this->ApiService->SendHTTPErroringResponse(422,$error);
        // }

        $redis_token = $this->UserController->checkLoginToken();
        if($redis_token['status'] == 'failed'){
            return $redis_token;
        }
        $token = $redis_token['token'];
        $ipo_url = $this->placementUrl($request);
        // $ipo_id = $request['ipo_id'];

        // INITIALZE REQUEST
        $data = $this->GenerateApiRequestResponseBody->ReturnTransactionsOrderData($token,$ipo_url,$request);

        // MAKE THE API CALL
        $api_response = $this->HttpProcessor->InitHttpPostRequestProcessor($data);

        // HANDLE RESPONSE WITH NULL RESPONSE DATA IN CASE - TOKEN EXPIRED\
        $response = $this->handlePlacementData($api_response,$request,$ipo_url);

        // RESPONSE AFTER IPO MASTER API CALL
        if($response['error_code'] != null){
            return $response;
        }
        return $response;
        // return $this->ApiService->SendHTTPSuccessResponse($response);
    }

    // NCB ORDER PLACEMENT
    public function NCBOrderPlacement(Request $request){

        // VALIDATIONS
        // $error = $this->ValidationModel->ValidateRequest($request);
        // if($error != null){
        //     return $this->ApiService->SendHTTPErroringResponse(422,$error);
        // }

        $redis_token = $this->UserController->checkLoginToken();
        if($redis_token['status'] == 'failed'){
            return $redis_token;
        }
        $token = $redis_token['token'];
        $ipo_url = $this->placementUrl($request);
        // $ipo_id = $request['ipo_id'];

        // INITIALZE REQUEST
        $data = $this->GenerateApiRequestResponseBody->ReturnNCBOrderPlacement($token,$ipo_url,$request);

        // MAKE THE API CALL
        $api_response = $this->HttpProcessor->InitHttpPostRequestProcessor($data);

        // HANDLE RESPONSE WITH NULL RESPONSE DATA IN CASE - TOKEN EXPIRED\
        $response = $this->handlePlacementData($api_response,$request,$ipo_url);

        // RESPONSE AFTER IPO MASTER API CALL
        if($response['error_code'] != null){
            return $response;
        }
        return $response;
        // return $this->ApiService->SendHTTPSuccessResponse($response);
    }

    // SGB ORDER PLACEMENT
    public function SGBOrderPlacement(Request $request){

        // VALIDATIONS
        // $error = $this->ValidationModel->ValidateRequest($request);
        // if($error != null){
        //     return $this->ApiService->SendHTTPErroringResponse(422,$error);
        // }

        $redis_token = $this->UserController->checkLoginToken();
        if($redis_token['status'] == 'failed'){
            return $redis_token;
        }
        $token = $redis_token['token'];
        $ipo_url = $this->placementUrl($request);
        // $ipo_id = $request['ipo_id'];

        // INITIALZE REQUEST
        $data = $this->GenerateApiRequestResponseBody->ReturnSGBOrderPlacement($token,$ipo_url,$request);

        // MAKE THE API CALL
        $api_response = $this->HttpProcessor->InitHttpPostRequestProcessor($data);

        // HANDLE RESPONSE WITH NULL RESPONSE DATA IN CASE - TOKEN EXPIRED\
        $response = $this->handlePlacementData($api_response,$request,$ipo_url);

        // RESPONSE AFTER IPO MASTER API CALL
        if($response['error_code'] != null){
            return $response;
        }
        return $response;
        // return $this->ApiService->SendHTTPSuccessResponse($response);
    }

    // HANDLE DATA
    public function handlePlacementData($api_response,$request,$ipo_url) {
        if(empty($api_response['data'])){
            $redis_token = $this->UserController->setLoginTokenExpire();
            $token = $redis_token['token'];
            $ipo_url = $this->placementUrl($request);
            $ipo_id = $request['ipo_id'];
            if($request['category_id'] == 1){
                $data = $this->GenerateApiRequestResponseBody->ReturnTransactionsOrderData($token,$ipo_url,$request);
            }
            else if($request['category_id'] == 2){
                $data = $this->GenerateApiRequestResponseBody->ReturnNCBOrderPlacement($token,$ipo_url,$request);
            }
            else if($request['category_id'] == 6){
                $data = $this->GenerateApiRequestResponseBody->ReturnSGBOrderPlacement($token,$ipo_url,$request);
            }
            $response = $this->HttpProcessor->InitHttpPostRequestProcessor($data);
            return $response;
        }
        else{
            return $api_response;
        }
    }

    public function placementUrl($request) {
        $secret_manager_data = $this->AwsService->getSecret();
        $ipo_url ='';
        if($request['category_id'] == 1){
            $ipo_url = $secret_manager_data['url'] . '/v1/transactions/add';
        }
        if($request['category_id'] == 2){
            $ipo_url = $secret_manager_data['url'] . '/v1/ncb/add';
        }
        if($request['category_id'] == 6){
            $ipo_url = $secret_manager_data['url'] . '/v1/sgb/add';
        }
        return $ipo_url;
    }
}
