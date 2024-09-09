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
use App\Http\Processor\api\SpProcessor;
use Exception;
use PDF;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use DateTime;

class APIController extends BaseController
{

    public function __construct(){
        parent::__construct();
        $this->RedisService = new RedisService();
        $this->UserController = new UserController();
        $this->SpProcessor = new SpProcessor();
    }
   
    public function AddNewInitiatorQuote(Request $ini_request){
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Frontend Request Newquote ' =>$ini_request->all()]);
        }
        $error = $this->ValidationModel->ValidateNewInitiatorQuote($ini_request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $NewInitiatorQuoteBody=$this->GenerateApiRequestResponseBody->CreateNewInitiatorQuoteBody($ini_request);

        $NewInitiatorQuoteInsertion=$this->SpProcessor->InsertNewInitiatorQuoteBody($NewInitiatorQuoteBody);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['NewInitiatorQuoteInsertion sp res'=>$NewInitiatorQuoteInsertion]);
        }
        if($NewInitiatorQuoteInsertion[0]->ret_status != 0 ){
           // return $this->ApiService->SendHTTPErrorResponse(423,config('error.1017'));
           return ["msg"=>"Order placement Failed","quote_id"=>0,"db_status"=>$NewInitiatorQuoteInsertion[0]->ret_status];
        }else{
           $order_id= $NewInitiatorQuoteInsertion[0]->order_id;
            return ["msg"=>"New Quote Added Successfully","quote_id"=>$order_id];// no major returns so handled lik this 
        }
    }

    public function ModifyOrder(Request $modify_request){
        //dd($Modify_request->All());
        $error = $this->ValidationModel->ValidateModifyOrder($modify_request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        $ModifyQuoteBody=$this->GenerateApiRequestResponseBody->CreateModifyQuoteBody($modify_request);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['ModifyQuoteBody'=>$ModifyQuoteBody]);
        }
        $ModifyQuoteInsertion=$this->SpProcessor->InsertModifyQuoteBody($ModifyQuoteBody);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['ModifyQuoteInsertion sp res'=>$ModifyQuoteInsertion]);
        }
        if($ModifyQuoteInsertion[0]->ret_status != 0 ){
            return ["msg"=>"Order Modification Failed","order_id"=>0,"db_status"=>$ModifyQuoteInsertion[0]->ret_status];
        }else{
            $order_id= $ModifyQuoteInsertion[0]->order_id;
            return ["msg"=>"Order Modified Successfully","order_id"=>$order_id];// no major returns so handled lik this 
        }
    }
    public function ProcessOrder(Request $request){
         if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Frontend Request' =>$request->all()]);
        }
        $error = $this->ValidationModel->ValidateProcessOrder($request);
        if($error != null){
            return $this->ApiService->SendHTTPErroringResponse(422,$error);
        }
        
        // $trade_no=279; //Testing
        //         $request['client_ip']="127.0.0.1";
        //         $AddRfqQuoteResponse[2]=894;
        //         $AddRfqQuoteResponse[3]=900;
        //         $shilpireporting = $this->ReportTradeToShilpi($trade_no,$request['client_ip'],$AddRfqQuoteResponse[2],$AddRfqQuoteResponse[3]);
        //         dd($shilpireporting);

        //Call AddRFQORDER
        $AddRfqAddResponse = $this->AddRfqOrderFunc($request);
        //dump($AddRfqAddResponse,"addrfqres");
        if(gettype($AddRfqAddResponse)!="array"){ // as in case of err we get resp as array
            $AddOrderResContent=json_decode($AddRfqAddResponse->content(),true);
        }else{
            $AddOrderResContent=$AddRfqAddResponse;
        }
        if($AddOrderResContent['error'] != null || $AddOrderResContent['status'] != 'success' ){
            return $AddOrderResContent;
        }else{
            $AddRfqQuoteResponse = $this->AddRfqQuoteFunc($request);
         //   dump($AddRfqQuoteResponse,"AddRfqQuoteResponse");
            if(isset($AddRfqQuoteResponse[0]) && gettype($AddRfqQuoteResponse[0])!="array"){
                $AcceptQuoteResContent=json_decode($AddRfqQuoteResponse[0]->content(),true);
            }else{
                $AcceptQuoteResContent=$AddRfqQuoteResponse;
            }
            if($AcceptQuoteResContent['error'] != null || $AcceptQuoteResContent['status'] != 'success'){
                return $AcceptQuoteResContent;
            }else{
                //get the trade id from quote response & then do shilpi reporting 
                if (isset($AddRfqQuoteResponse[1]) && $AddRfqQuoteResponse[1]!=0){
                     // $shilpireporting = $this->ReportTradeToShilpi($AddRfqQuoteResponse[1],$request['client_ip'],$AddRfqQuoteResponse[2],$AddRfqQuoteResponse[3]);
   
                    $dealslipResponse = $this->GenerateDealSlip($AddRfqQuoteResponse[1],$AddRfqQuoteResponse[3],$AddRfqQuoteResponse[2]);// get this from quote ka response as of now hardcode it 

                    $final_response=[
                        'AcceptQuoteOrderId'=>$AcceptQuoteResContent['data']['RFQQuoteAcceptResponceList'][0]['Ordernumber'],
                        'trade_no'=>$AddRfqQuoteResponse[1],
                    ];
                    return $this->ApiService->SendHTTPSuccessResponse($final_response);
                }else{
                    return $this->ApiService->SendHTTPErrorResponse(423,config('error.1001'));
                }
            }
        }
    }
    // IPOMASTER API
    public function AddRfqOrderFunc($request){

        $response = $this->UserController->checkLoginToken($request['client_ip']);
        if($response['error_code'] != null){
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['loginerraddrfqorder'=>$response]);
            }
            return $response;
        }

        //Add responder entry in orderbook table
        $ResponderOrdbookEntryBody=$this->GenerateApiRequestResponseBody->CreateResponderOrdbookEntryBody($request);
        $ResponderOrdbookinsertion=$this->SpProcessor->InsertResponderOrdbookEntryBody($ResponderOrdbookEntryBody);

        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['ResponderOrdbookinsertion sp res'=>$ResponderOrdbookinsertion]);
        }
        if(!isset($ResponderOrdbookinsertion[0]->trade_id) || $ResponderOrdbookinsertion[0]->trade_id == '' ){
            return $this->ApiService->SendHTTPErrorResponse(423,config('error.1001'));//424
        }
        $req_time=date('Y-m-d H:i:s');
        $secretmangerdata = $this->getSecretManagerDetails("addorder");
        $headers = $this->ApiService->GenerateStandardHTTPHeader("addorder",$secretmangerdata,$response['token']);
        $req = $this->GenerateApiRequestResponseBody->ReturnAddRfqRequestData($headers,$secretmangerdata,$request);
        // generate checksum header 
        $getchecksum = $this->generateCheckSum("AddRFQOrder",$secretmangerdata,$response['token'],json_encode($req['body']),$request->client_ip);
        
        $req["headers"]["CHECKSUM"] = $getchecksum;
        

        // MAKE THE API CALL
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Rfqaddordersapireq'=>$req]);
        }
        $api_response = $this->HttpProcessor->InitHttpPostRequestProcessor($req);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Rfqaddordersapiresponse'=>$api_response]);
        }
        $res_time=date('Y-m-d H:i:s');
        //Save Logs
        $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($api_response['data']['RFQOrderResponceList'][0]['MESSAGE'],$req,$api_response,$secretmangerdata['data']['api_url'],"AddRFQAccept",$ResponderOrdbookinsertion[0]->trade_id,$req_time,$res_time,$request['client_ip']);
        $loginsertion=$this->SpProcessor->InsertAPILogs($logs);

         /* Call only if token is invalid - as in that case we need to relogin & thne try api hit again */
        
        //  $api_response['data']['RFQOrderResponceList'][0]['MESSAGE']="Invalid Bank Details";
        //  $api_response['data']['RFQOrderResponceList'][0]['ERRORCODE']=1;
        if((isset($api_response['data']['RFQOrderResponceList'][0]['ERRORCODE']))&& $api_response['data']['RFQOrderResponceList'][0]['MESSAGE']=="Invalid Token"){
            $response = $this->handleData("addorder",$api_response,$request,$secretmangerdata,$ResponderOrdbookinsertion[0]->trade_id);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['Rfqaddorders_handleDatacallerresp'=>$response]);
            }
            //  $response['data']['RFQOrderResponceList'][0]['MESSAGE']="Invalid Token";
            //  $response['data']['RFQOrderResponceList'][0]['ERRORCODE']=1;
            if((isset($response['data']['RFQOrderResponceList'][0]['ERRORCODE'])&& $response['data']['RFQOrderResponceList'][0]['ERRORCODE']!= 0) ||$response["error_code"]!=null){
                $system_error=config('error.1002');
                $error=$system_error." Reason- ".$response['data']['RFQOrderResponceList'][0]['MESSAGE'];

                //call sp to revert the qty & status 
                $childOrderid=$ResponderOrdbookinsertion[0]->order_id;
                $trade_id=$ResponderOrdbookinsertion[0]->trade_id;

                $RevertBody=$this->GenerateApiRequestResponseBody->CreateRevertBody($request,$childOrderid,$trade_id);

                $RevertQtyAndStatusResp=$this->SpProcessor->RevertQtyAndStatus($RevertBody); 
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['RevertQtyAndStatusResp sp res'=>$RevertQtyAndStatusResp]);
                }
                if($RevertQtyAndStatusResp[0]->ret_status!=0){
                    return $this->ApiService->SendHTTPErrorResponse(423,config('error.1019'));//424
                }
                //return            
                return $this->ApiService->SendHTTPErrorResponse(428,$error);
            }
        }else if((isset($api_response['data']['RFQOrderResponceList'][0]['ERRORCODE'])&& $api_response['data']['RFQOrderResponceList'][0]['ERRORCODE']!= 0)){
            $system_error=config('error.1002');
            $error=$system_error." Reason- ".$api_response['data']['RFQOrderResponceList'][0]['MESSAGE'];

                //call sp to revert the qty & status 
                $childOrderid=$ResponderOrdbookinsertion[0]->order_id;
                $trade_id=$ResponderOrdbookinsertion[0]->trade_id;

                $RevertBody=$this->GenerateApiRequestResponseBody->CreateRevertBody($request,$childOrderid,$trade_id);
              //  dd($RevertBody,"qty");
                $RevertQtyAndStatusResp=$this->SpProcessor->RevertQtyAndStatus($RevertBody); 
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['RevertQtyAndStatusRespnormal sp res'=>$RevertQtyAndStatusResp]);
                }
                if($RevertQtyAndStatusResp[0]->ret_status!=0){
                    return $this->ApiService->SendHTTPErrorResponse(423,config('error.1019'));//424
                }
            return $this->ApiService->SendHTTPErrorResponse(428,$error);
        }else{
            $response=$api_response;
        }       

        //Insert in API Specific tables
      
        $RfqAddBody=$this->GenerateApiRequestResponseBody->CreateAPIInsertBody("AddRFQOrder",$request,$response['data']['RFQOrderResponceList'][0]);
        $RfqAddDbInsert=$this->SpProcessor->InsertDetailsInAddRfqOrderAPITable($RfqAddBody);
        
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['RfqAddDbInsert sp res'=>$RfqAddDbInsert]);
        }

        // if(isset($RfqAddDbInsert["error"]) && $RfqAddDbInsert["error"]!='' || $RfqAddDbInsert[0]->ret_status != 0 ){
        //     //Just log the error and go ahead as we dont want to stop the process
        //     return $this->ApiService->SendHTTPErrorResponse(424,config('error.1012'));
        // }

        //Add RfqAddOrderResponse params need for hitting AcceptRfqQuote
        $request->request->add([
            'AcceptOrderDealID' => isset($response['data']['RFQOrderResponceList'][0]['RFQDealID'])?$response['data']['RFQOrderResponceList'][0]['RFQDealID']:null,
            'AcceptOrderNo' => isset($response['data']['RFQOrderResponceList'][0]['RFQOrdernumber'])?$response['data']['RFQOrderResponceList'][0]['RFQOrdernumber']:null,
            'InternalOrderNumber'=>isset($response['data']['RFQOrderResponceList'][0]['InternalOrderNumber'])?$response['data']['RFQOrderResponceList'][0]['InternalOrderNumber']:null,
            'trade_id'=>isset($ResponderOrdbookinsertion[0]->trade_id)?$ResponderOrdbookinsertion[0]->trade_id:null,// as this is needed for asddfqquote also so adding here
            'childOrderid'=>isset($ResponderOrdbookinsertion[0]->order_id)?$ResponderOrdbookinsertion[0]->order_id:null,

        ]);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['RfqAcceptQuoteInputdata'=>$request->all()]);
        }

        // Update tradebook response
        $TradebookBodyUpdate=$this->SpProcessor->updateTradebook($request->all(),"addorder");
        // dd($TradebookBodyUpdate);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['TradebookBodyUpdate sp res'=>$TradebookBodyUpdate]);
        }
      
        if (isset($TradebookBodyUpdate->ret_status[0]) && $TradebookBodyUpdate->ret_status[0]!=0){
         //   dd("here coming");
            return $this->ApiService->SendHTTPErrorResponse(428,config('error.1009'));
        }
        return $this->ApiService->SendHTTPSuccessResponse($response);
    }

    public function AddRfqQuoteFunc($request){
      
        $response = $this->UserController->checkLoginToken($request['client_ip']);
        if($response['error_code'] != null){
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['AddRfqQuoteFunc'=>$response]);
            }
            return $response;
        }
        $req_time=date('Y-m-d H:i:s');
        $secretmangerdata = $this->getSecretManagerDetails("acceptquote");
        $headers = $this->ApiService->GenerateStandardHTTPHeader("acceptquote",$secretmangerdata,$response['token']);
        $req = $this->GenerateApiRequestResponseBody->ReturnRfqQuoteRequestData($headers,$secretmangerdata,$request);
        // generate checksum 

        $getchecksum = $this->generateCheckSum("RFQQuoteAccept",$secretmangerdata,$response['token'],json_encode($req['body']),$request->client_ip);
        
        $req["headers"]["CHECKSUM"] = $getchecksum;
        // MAKE THE API CALL
        // dd($req,"req");
        // MAKE THE API CALL
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Rfqacceptquotesapireq'=>$req]);
        }
        $api_response = $this->HttpProcessor->InitHttpPostRequestProcessor($req);
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['Rfqacceptquotesapiresponse'=>$api_response]);
        }
        $res_time=date('Y-m-d H:i:s');
        //Save Logs
        $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($api_response['data']['RFQQuoteAcceptResponceList'][0]['MESSAGE'],$req,$api_response,$secretmangerdata['data']['api_url'],"RFQQuoteAccept",$request['trade_id'],$req_time,$res_time,$request['client_ip']);
        $loginsertion=$this->SpProcessor->InsertAPILogs($logs);

        // HANDLE RESPONSE WITH NULL RESPONSE DATA IN CASE - TOKEN EXPIRED\

        // dd("check",$response['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE']);
         /* Call only if token is invalid - as in that case we need to relogin & thne try api hit again */
        if((isset($api_response['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE']))&& $api_response['data']['RFQQuoteAcceptResponceList'][0]['MESSAGE']=="Invalid Token"){
            $response = $this->handleData("acceptquote",$api_response,$request,$secretmangerdata,$request['trade_id']);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['Rfqacceptquote_handleDatacallerresp'=>$response]);
            }
            if((isset($response['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE'])&& $response['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE']!= "0") ||$response["error_code"]!=null){
                $system_error=config('error.1008');
                $error=$system_error." Reason- ".$response['data']['RFQQuoteAcceptResponceList'][0]['MESSAGE'];


                //call sp to revert the qty & status 
                $childOrderid=$request->childOrderid;
                $trade_id=$request->trade_id;

                $RevertBody=$this->GenerateApiRequestResponseBody->CreateRevertBody($request,$childOrderid,$trade_id);
              //  dd($RevertBody,"qty");
                $RevertQtyAndStatusResp=$this->SpProcessor->RevertQtyAndStatus($RevertBody); 
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['RevertQtyAndStatusRespQuoteaccept sp res'=>$RevertQtyAndStatusResp]);
                }
                if($RevertQtyAndStatusResp[0]->ret_status!=0){
                    return $this->ApiService->SendHTTPErrorResponse(423,config('error.1019'));//424
                }

                return $this->ApiService->SendHTTPErrorResponse("427",$error);
            }
        }else if((isset($api_response['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE'])&& $api_response['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE']!= "0")){
            $system_error=config('error.1008');
            $error=$system_error." Reason- ".$api_response['data']['RFQQuoteAcceptResponceList'][0]['MESSAGE'];

                //call sp to revert the qty & status 
                $childOrderid=$request->childOrderid;
                $trade_id=$request->trade_id;

                $RevertBody=$this->GenerateApiRequestResponseBody->CreateRevertBody($request,$childOrderid,$trade_id);
              //  dd($RevertBody,"qty");
                $RevertQtyAndStatusResp=$this->SpProcessor->RevertQtyAndStatus($RevertBody); 
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['RevertQtyAndStatusRespQuoteaccept sp res'=>$RevertQtyAndStatusResp]);
                }
                if($RevertQtyAndStatusResp[0]->ret_status!=0){
                    return $this->ApiService->SendHTTPErrorResponse(423,config('error.1019'));//424
                }
            return $this->ApiService->SendHTTPErrorResponse("427",$error);
            
        }else{
            $response=$api_response;
        }
        //Insert in API Specific tables
        $RfqAddBody=$this->GenerateApiRequestResponseBody->CreateAPIInsertBody("RFQAcceptQuote",$request,$response['data']['RFQQuoteAcceptResponceList'][0]);
        $RfqAcceptDbInsert=$this->SpProcessor->InsertDetailsInAcceptQuoteAPITable($RfqAddBody);

        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['RfqAcceptDbInsert sp res'=>$RfqAcceptDbInsert]);
        }

        // if(isset($RfqAcceptDbInsert["error"]) && $RfqAcceptDbInsert["error"]!='' || $RfqAcceptDbInsert[0]->ret_status != 0 ){
        //     return $this->ApiService->SendHTTPErrorResponse(425,config('error.1013'));
        // }

        //Update tradebook response
        $quoteData=[
        'trade_id'=>$request->trade_id,
        'user_id'=>$request->user_id,
        'user_ucc'=>$request->trade_ucc,
        'quoteDealid'=>$response['data']['RFQQuoteAcceptResponceList'][0]['RFQDealID'],
        'quoteRFQOrderno'=>$response['data']['RFQQuoteAcceptResponceList'][0]['RFQOrdernumber'],
        'quoteFinalOrderNo'=>$response['data']['RFQQuoteAcceptResponceList'][0]['Ordernumber']
        ];

        $TradebookBodyUpdatequote=$this->SpProcessor->updateTradebook($quoteData,"acceptquote");
       // dd($TradebookBodyUpdatequote);
        if (isset($TradebookBodyUpdate->ret_status[0]) &&$TradebookBodyUpdate->ret_status[0]!=0){
            return $this->ApiService->SendHTTPErrorResponse(428,config('error.1009'));
        }
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['TradebookBodyUpdatequote sp res'=>$TradebookBodyUpdatequote]);
        }
        return [$this->ApiService->SendHTTPSuccessResponse($response),
            $request->trade_id,
            $request->childOrderid,
            $request->InternalOrderNumber
        ];//need to return tradeid,childOrderid & InternalOrderNumber as its needed by deal slip and deal book
    }
    
    public function ReportTradeToShilpi($trade_no,$client_ip,$parentOrderId,$childOrderId){
       // $trade_no=1;//as of now setting value hardcode 
        $tradeDetails=$this->SpProcessor->getTradeDetails($trade_no);// for flags checking we need tradebook
        if(!empty($tradeDetails)){
                $shilpidetails_ini=$this->SpProcessor->getDealBookDetails($trade_no,$parentOrderId);
                $shilpidetails_resp=$this->SpProcessor->getDealBookDetails($trade_no,$childOrderId); 

           
                if($tradeDetails[0]->shilpi_reported_initiator==0 && $tradeDetails[0]->shilpi_reported_responder==0 ){
                //return "comig";
                $isShilpiReportedInitiator=0;
                $isShilpiReportedResponder=0;
                $req_time_shilpi_initiator=date('Y-m-d H:i:s');

                $initiatorShilpiRequest=$this->GenerateApiRequestResponseBody->CreateShilpiReqBody($shilpidetails_ini,"initiator");

                $req_time_shilpi_responder=date('Y-m-d H:i:s');

                $responderShilpiRequest=$this->GenerateApiRequestResponseBody->CreateShilpiReqBody($shilpidetails_resp,"responder");

                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['initiatorShilpiRequest'=>$initiatorShilpiRequest]);
                    Log::channel("response")->info(['responderShilpiRequest'=>$responderShilpiRequest]);
                }
        
               //hit the api, try block is added because many times shilpi gives error of connection refused

                try{
                    $api_response_initiator = $this->HttpProcessor->InitHttpGetRequestProcessor($initiatorShilpiRequest,true);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['initiatorShilpiRes'=>$api_response_initiator]);
                    }
                }catch(Exception $e){
                    $api_response_initiator=[];
                    Log::channel("response")->info(['Initiator Shilpi exception occured'=>$e->getMessage()]);
                }
                $resp_time_shilpi_initiator=date('Y-m-d H:i:s');
                if(isset($api_response_initiator["is_success"]) && $api_response_initiator["is_success"] == "true"){
                    /*when we get error $api_response_initiator["data"]="error-1000" whose decode will be blank henceif it happens i am puttin error word*/
                    if(!Str::contains($api_response_initiator['data'], 'ERROR') && !Str::contains($api_response_initiator['data'], 'Fail')) {
                        $isShilpiReportedInitiator=1;
                        $decodedresponse_ini=json_decode($api_response_initiator['data'],true);
                        $inimsg=$decodedresponse_ini["Status"];
                    }else{
                        $decodedresponse_ini=$api_response_initiator['data'];
                        $inimsg=$api_response_initiator['data'];
                    }  
                }else{
                    $inimsg="API Call Failed";
                }
                try{
                    $api_response_responder=$this->HttpProcessor->InitHttpGetRequestProcessor($responderShilpiRequest,true);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['responderShilpiRequest'=>$api_response_responder]);
                    }
                }catch(Exception $e){
                    $api_response_responder=[];
                    Log::channel("response")->info(['Responder Shilpi exception occured'=>$e->getMessage()]);
                }
                $resp_time_shilpi_responder=date('Y-m-d H:i:s');

                if(isset($api_response_responder["is_success"]) && $api_response_responder["is_success"] == "true"){
                    /*when we get error $api_response_initiator["data"]="error-1000" whose decode will be blank henceif it happens i am puttin error word*/
                    if(!Str::contains($api_response_responder['data'], 'ERROR') && !Str::contains($api_response_responder['data'], 'Fail')) {
                        $isShilpiReportedResponder=1;
                        $decodedresponse_res=json_decode($api_response_responder['data'],true);
                        $resmsg=$decodedresponse_res["Status"];
                    }else{
                        $decodedresponse_res=$api_response_responder['data'];
                        $resmsg=$api_response_responder['data'];
                    }  
                }else{
                    $resmsg="API Call Failed";
                }
                //initiator logs
                $initiator_logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($inimsg,$initiatorShilpiRequest,$api_response_initiator,config("constant.PROD_SHLIPIURL"),"ShilpiInitiatorApi",$trade_no,$req_time_shilpi_initiator,$resp_time_shilpi_initiator,$client_ip);
                $initiator_loginsertion=$this->SpProcessor->InsertAPILogs($initiator_logs);
        
                //responder logs
                $responder_logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($resmsg,$responderShilpiRequest,$api_response_responder,config("constant.PROD_SHLIPIURL"),"ShilpiRespoderApi",$trade_no,$req_time_shilpi_responder,$resp_time_shilpi_responder,$client_ip);
                $responder_loginsertion=$this->SpProcessor->InsertAPILogs($responder_logs);

                // if($isShilpiReportedResponder==1 && $isShilpiReportedInitiator==1 ){

                $UpdateDealbookFlagsInitiator=$this->SpProcessor->UpdateDealbookFlagsShilpiTable($trade_no,$tradeDetails[0]->init_order_id,$isShilpiReportedInitiator);
                $UpdateDealbookFlagsResponder=$this->SpProcessor->UpdateDealbookFlagsShilpiTable($trade_no,$tradeDetails[0]->res_order_id,$isShilpiReportedResponder);

                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['UpdateDealbookFlagsInitiator sp res'=>$UpdateDealbookFlagsInitiator]);
                    Log::channel("query")->info(['UpdateDealbookFlagsResponder sp res'=>$UpdateDealbookFlagsResponder]);
                }

                $dbresinit=isset($UpdateDealbookFlagsInitiator[0]->ret_status)?$UpdateDealbookFlagsInitiator[0]->ret_status:null;
                $dbresresponder=isset($UpdateDealbookFlagsResponder[0]->ret_status)?$UpdateDealbookFlagsResponder[0]->ret_status:null;
                //}
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['shilpi initiator flag'=>$isShilpiReportedInitiator,'shilpi responder flag'=>$isShilpiReportedResponder]);
                }
                return [$isShilpiReportedInitiator,$isShilpiReportedResponder];
            }
            else{
                return $this->ApiService->SendHTTPErrorResponse("429",config('error.1005'));
            }
        }else{
            return $this->ApiService->SendHTTPErrorResponse("429",config('error.1021'));
        }
       
    }

    public function GenerateDealSlip($trade_no,$parentOrderId,$childOrderId){
        //fetch the required data from db 
        $tradeDetails=$this->SpProcessor->getTradeDetails($trade_no);

        if($tradeDetails[0]->is_initiator_email_sent==0 && $tradeDetails[0]->is_responder_email_sent==0 
        && $tradeDetails[0]->is_initiator_sms_sent==0  && $tradeDetails[0]->is_responder_sms_sent==0 )
        {

            $dealslipdetails_ini=$this->SpProcessor->getDealBookDetails($trade_no,$parentOrderId);
            $dealslipdetails_resp=$this->SpProcessor->getDealBookDetails($trade_no,$childOrderId); 

            //Create initiator & reponder body
            $initiator_details=$this->GenerateApiRequestResponseBody->CreateDealSlipBody($dealslipdetails_ini,"initiator",$tradeDetails);
            $responder_details=$this->GenerateApiRequestResponseBody->CreateDealSlipBody($dealslipdetails_resp,"responder",$tradeDetails);

            $TodayDate=date('Ymd');
            // initiator_file upload

            $formatted_setlment_amt=$this->money_format_conversion($initiator_details['settlement_amount']);
            //setting new formatted value
            Log::channel("response")->info(["here"=>$formatted_setlment_amt]);
            $initiator_details['settlement_amount']=$formatted_setlment_amt;

            $initiator_pdf = PDF::loadView('initiatordealslippdf', ['initiator_details'=>$initiator_details]);
            // $customPaper = array(0,0,800,1200);
            // $initiator_pdf->setPaper($customPaper);
            $init_content = $initiator_pdf->output();
            $init_filename="Init_Dealslip_".$initiator_details['order_no']."_".$initiator_details['trade_no'].'.pdf';
            $initiator_path = Storage::disk('s3')->put("RFQ/$TodayDate/$init_filename", $init_content);
            $initiator_path = Storage::disk('s3')->url($initiator_path);

            //responder _file upload
            $formatted_setlment_amt_resp=$this->money_format_conversion($responder_details['settlement_amount']);
            //setting new formatted value
            $responder_details['settlement_amount']=$formatted_setlment_amt_resp;

            $responder_pdf = PDF::loadView('responderdealslippdf', ['responder_details'=>$responder_details]);
            // $customPaper = array(0,0,800,1200);
            // $responder_pdf->setPaper($customPaper);
            $resp_content = $responder_pdf->output();
            $resp_filename="Resp_Dealslip_".$responder_details['order_no']."_".$responder_details['trade_no'].'.pdf';
            $resp_path = Storage::disk('s3')->put("RFQ/$TodayDate/$resp_filename", $resp_content);
            $resp_path = Storage::disk('s3')->url($resp_path);
           
            //update filepath in db
            $UpdateDealSlipPathInitiator=$this->SpProcessor->UpdateDilslipFilePath($trade_no,$tradeDetails[0]->init_order_id,"RFQ/$TodayDate/$init_filename");
            $UpdateDealSlipPathResponder=$this->SpProcessor->UpdateDilslipFilePath($trade_no,$tradeDetails[0]->res_order_id,"RFQ/$TodayDate/$resp_filename");
     
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['UpdateDealSlipPathInitiator sp res'=>$UpdateDealSlipPathInitiator]);
                Log::channel("query")->info(['UpdateDealSlipPathResponder sp res'=>$UpdateDealSlipPathResponder]);
            }
     
            $dbrespathinit=isset($UpdateDealSlipPathInitiator[0]->ret_status)?$UpdateDealSlipPathInitiator[0]->ret_status:null;
            $dbrespathresponder=isset($UpdateDealSlipPathResponder[0]->ret_status)?$UpdateDealSlipPathResponder[0]->ret_status:null;

            $NotificationTrigger=$this->emailSmsTrigger($trade_no,"RFQ/$TodayDate/$init_filename","RFQ/$TodayDate/$resp_filename");
            if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['emailSmsTrigger res'=>$NotificationTrigger]);
                }
            //update notification flags in db 
                $flagData=[
                    'trade_id'=>$tradeDetails[0]->trade_id,
                    'user_id'=>$tradeDetails[0]->res_user_id,
                    'initiator_email'=>$NotificationTrigger['ISInitiatorEmailsent'],
                    'initiator_sms'=>$NotificationTrigger['ISInitiatorSMSsent'],
                    'responder_sms'=>$NotificationTrigger['ISResponderSmsSent'],
                    'responder_email'=>$NotificationTrigger['ISResponderEmailsent']
                ];
            
                $TradebookBodyUpdateflags=$this->SpProcessor->updateFlagsInTradebook($flagData);
                   // dd($TradebookBodyUpdateflags);
                if (isset($TradebookBodyUpdateflags->ret_status[0]) && $TradebookBodyUpdateflags->ret_status[0]!=0){
                    return $this->ApiService->SendHTTPErrorResponse(428,config('error.1009'));
                }
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['TradebookBodyUpdateflags sp res'=>$TradebookBodyUpdateflags]);
                }
            return [$dbrespathinit,$dbrespathresponder,$NotificationTrigger];
        }else{
            return $this->ApiService->SendHTTPErrorResponse("429",config('error.1014'));
        }
    }
    
    public function emailSmsTrigger($trade_no ,$init_path ,$res_path ) {
        try {
            $tradeDetails = $this->SpProcessor->getTradeDetails($trade_no)[0];
                //   dd($tradeDetails);
            $init_user_id = $tradeDetails->init_user_id;
            $res_user_id = $tradeDetails->res_user_id;
      
            // Fetch initiator and responder details
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['emailsmstriggerInitiatorUserDetails req'=>"sp_get_userinfo_by_id_nse_rfq(".$init_user_id.")"]);
            }
            $initiator_user_details = DB::select("call bondbazaar.sp_get_userinfo_by_id_nse_rfq($init_user_id)");
         
            $init_user_details = $initiator_user_details[0];
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['emailsmstriggerInitiatorUserDetails res'=>$init_user_details]);
            }
  
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['emailsmstriggerInitiatorUserDetails req'=>"sp_get_userinfo_by_id_nse_rfq(".$res_user_id.")"]);
            }
            $responder_user_details = DB::select("call bondbazaar.sp_get_userinfo_by_id_nse_rfq($res_user_id)");
            $res_user_details = $responder_user_details[0];
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['emailsmstriggerResponderUserDetails res'=>$res_user_details]);
            }
      
            // Initialize default values
            $response = [
                "ISInitiatorEmailsent" => 0,
                "ISInitiatorSMSsent" => 0,
                "ISResponderEmailsent" => 0,
                "ISResponderSmsSent" => 0,
                "ISInitiatorEmailSentFailure" => null,
                "ISResponderEmailSentFailure" => null,
                "ISInitiatorSmsSentFailure" => null,
                "ISResponderSmsSentFailure" => null,
                "ISsmsSentFailure" => null,
                "ISemailSentFailure" => null,
            ];
            
            // Check if emails are not already sent
            if ($tradeDetails->is_initiator_email_sent == "0" && $tradeDetails->is_responder_email_sent == '0') {
                $type = "EMAIL";
                // Send initiator email
                $flag = 'initiator';
                $init_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipEmailData($tradeDetails, $init_user_details, $init_path , $flag);
                $init_json = json_decode($init_json,true);
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['emailsmstriggerInitiatorMailResponse req'=>$init_json]);
                } 
                    try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
                        $mail_response = $this->sendEmail($init_json);
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['mail_response'=>$mail_response]);
                        } 
                        $end_time  = $this->getCurrentTimestampWithMicroseconds();
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['emailsmstriggerInitiatorMailResponse res'=>$mail_response]);
                        } 
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$mail_response['url'],json_encode($init_json),$type,json_encode($mail_response['response']),$start_time, $end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                    
                        if (isset($mail_response['response']) && $mail_response['response']['code'] == "200") {
                            $response["ISInitiatorEmailsent"] = 1;
                        } else {
                            $response["ISInitiatorEmailSentFailure"] = $mail_response['message'] ?? 'No response found';
                            Log::channel("response")->info(['InitiatorEmail Exception' => 'No response found']);

                        }
                        } catch (\Throwable $th) {
                            Log::channel("response")->info(['InitiatorEmail Exception' => $th->getMessage()]);
                            $response["ISInitiatorEmailSentFailure"] = $th->getMessage();
                    }
            
                    // Send responder email
                    $flag = 'responder';
                    $res_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipEmailData($tradeDetails, $res_user_details, $res_path ,$flag);
                    $res_json = json_decode($res_json,true);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['emailsmstriggerResponderMailResponse req'=>$res_json]);
                    } 

                    try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
                        $res_mail_response = $this->sendEmail($res_json);
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['res_mail_response'=>$res_mail_response]);
                        }
                        $end_time = $this->getCurrentTimestampWithMicroseconds();
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['emailsmstriggerResponderMailResponse res'=>$res_mail_response]);
                        }
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$res_mail_response['url'],json_encode($res_json),$type,json_encode($res_mail_response['response']),$start_time,$end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                    
                        if (isset($res_mail_response['response']) && $res_mail_response['response']['code'] == "200") {
                            $response["ISResponderEmailsent"] = 1;
                        } else {
                            $response["ISResponderEmailSentFailure"] = $res_mail_response['message'] ?? 'No response found';
                            Log::channel("response")->info(['ISResponderEmailSent Exception' => 'No response found']);
                        }
                    } catch (\Throwable $th) {
                        Log::channel("response")->info(['ResponderEmail Exception' => $th->getMessage()]);
                        $response["ISResponderEmailSentFailure"] = $th->getMessage();
                    } 
                
            } else {
                $response["ISemailSentFailure"] = 'Email already sent';
            }
            // Check if SMSs are not already sent
            if ($tradeDetails->is_initiator_sms_sent == '0' && $tradeDetails->is_responder_sms_sent == '0') {
                $type = 'SMS';
                // Send initiator SMS
                $flag = 'initiator';
                $initSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($tradeDetails,$init_user_details,$flag);
    
                $initSms_json = json_decode($initSms_json,true);
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['emailsmstriggerInitiatorMSMSResponseSpResponse req'=>$initSms_json]);
                } 

                    try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
        
                        $sms_response = $this->sendSMS($initSms_json);
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['sms_response'=>$sms_response]);
                        }
                        $end_time = $this->getCurrentTimestampWithMicroseconds();
            
            
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['emailsmstriggerInitiatorMSMSResponseSpResponse res'=>$sms_response]);
                        } 
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$sms_response['url'],json_encode($initSms_json),$type,json_encode($sms_response['response']),$start_time,$end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                        if (isset($sms_response['response']) && $sms_response['response']['code'] == "200") {
                            $response["ISInitiatorSMSsent"] = 1;
                        } else {
                            $response["ISInitiatorSmsSentFailure"] = $sms_response['message'] ?? 'No response found';
                            Log::channel("response")->info(['ISInitiatorSmsSentFailure Exception' => 'No response found']);

                        }
                    } catch (\Throwable $th) {

                        Log::channel("response")->info(['ISInitiatorSmsSentFailure Exception' => $th->getMessage()]);
                        $response["ISInitiatorSmsSentFailure"] = $th->getMessage();
                    }
                
        
                // Send responder SMS
                $flag = 'responder';
                $respSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($tradeDetails,$res_user_details,$flag);
                $respSms_json = json_decode($respSms_json,true);
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("response")->info(['emailsmstriggerResponderSMSResponse req'=>$respSms_json]);
                }
                try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
                        $res_sms_response = $this->sendSMS($respSms_json);
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['res_sms_response'=>$res_sms_response]);
                        }
                        $end_time = $this->getCurrentTimestampWithMicroseconds();
            
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['emailsmstriggerResponderSMSResponse res'=>$res_sms_response]);
                        }
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$res_sms_response['url'],json_encode($respSms_json),$type,json_encode($res_sms_response['response']),$start_time,$end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                    
                        if (isset($res_sms_response['response']['code']) && $res_sms_response['response']['code'] == "200") {
                            $response["ISResponderSmsSent"] = 1;
                        } else {
                            $response["ISResponderSmsSentFailure"] = $res_sms_response['message'] ?? 'No response found';
                            Log::channel("response")->info(['ISResponderSmsSentFailure Exception' => 'No response found']);

                        }
                    } catch (\Throwable $th) {
                        Log::channel("response")->info(['ISResponderSmsSentFailure Exception' => $th->getMessage()]);
                        $response["ISResponderSmsSentFailure"] = $th->getMessage();
                }
                
            } else {
                $response["ISsmsSentFailure"] = 'SMS already sent';
            }
      
            return $response;
        }catch (\Throwable $th) {
          Log::channel("response")->info(['EmailSmsTrigger Exception '=>$th]);
        }
    }

    public function adminEmailSmsTrigger(Request $request){
       try {
            $response = [
                'success' => false,
            ];
            $trade_no = $request->trade_id ?? '19';
            $flag = $request->flag ?? 'initiator';
            $tradeDetails=$this->SpProcessor->getTradeDetails($trade_no)[0]; 
            $init_user_id = $tradeDetails->init_user_id;
          
            $res_user_id = $tradeDetails->res_user_id;
            $flagData['user_id'] = null;
            $flagData['trade_id'] = $trade_no;
            $flagData['initiator_email'] = null;
            $flagData['responder_email'] = null;
            $flagData['initiator_sms'] = null;
            $flagData['responder_sms'] = null;
        
            if($flag == 'initiator'){
                $flag = 'initiator';
                $init_order_id =$tradeDetails->init_order_id;

                $init_path = DB::select('call sp_get_deal_slip_file_path_rfq(?, ?)', [$init_order_id, $trade_no]);
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['AdminEmailsmstriggerInitPath res'=>$init_path]);
                }
                if(!empty($init_path)){
                    $init_path = $init_path[0]->file_path;
                }else{
                    $response['success'] = false;
                    $response['message'] = 'Dealslip does not exist.';
                    return $response;
                }
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['AdminEmailsmstriggerInitiatorUserDetails req'=>$init_user_id]);
                }
                $initiator_user_details=DB::select("call bondbazaar.sp_get_userinfo_by_id_nse_rfq(".$init_user_id.")");
                $init_user_details = $initiator_user_details[0];
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['AdminEmailsmstriggerInitiatorUserDetails res'=>$init_user_details]);
                }
                if($tradeDetails->is_initiator_email_sent == "0"){
                    $type = "INITIATOR ADMIN EMAIL";
                    // +++++++++++=inititator EMAIL++++++++++++++++++++++++++++++++++++++++++
                    $init_json=$this->GenerateApiRequestResponseBody->ReturnDealSlipEmailData($tradeDetails,$init_user_details,$init_path,$flag);
                    $init_json = json_decode($init_json,true);
                    // Call common email service
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMailResponse req'=>$init_json]);
                    } 
                    try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
                        $mail_response = $this->sendEmail($init_json);
                        $end_time = $this->getCurrentTimestampWithMicroseconds();
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMailResponse res'=>$mail_response]);
                        } 
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$mail_response['url'],json_encode($init_json),$type,json_encode($mail_response['response']),$start_time,$end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                        
                        if(isset($mail_response['response']) && $mail_response['response']['code'] == "200"){
                            $response['success'] = true;
                            $flagData['initiator_email'] = 1;
                            $response['email_success'] = true;
                        }else{
                            $response['success'] = false;
                            $response['email_failure_msg'] =$mail_response['message'] ?? 'No response found';
                            $response['email_success'] = false;
                            Log::channel("response")->info(['Admin INIT Email Exception' => 'No response found']);

                        }
                    } catch (\Throwable $th) {
                        Log::channel("response")->info(['Admin INIT Email Exception' => $th->getMessage()]);
                        $response = [
                            'success' => false,
                            'message' =>  $th->getMessage(),
                        ];
                    }
                   
                    //+++++++++++++++++++++ Responder EMAIL +++++++++++++++++++++++++++++++++++++++++++
                }else{
                    $response = [
                        'success' => false,
                        'message' => 'Mail sent already.',
                    ];
                }
                if($tradeDetails->is_initiator_sms_sent == '0'){
                    $type = 'INITIATOR ADMIN SMS';
                    // ++++++++++++++++++++Initiator SMS++++++++++++++++++++++++++++++++++
                    $initSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($tradeDetails,$init_user_details,$flag);
                    $initSms_json = json_decode($initSms_json,true);
                    // Call common SMS service
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse req'=>$initSms_json]);
                    } 
                    try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
                        $sms_response = $this->sendSMS($initSms_json);
                        $end_time = $this->getCurrentTimestampWithMicroseconds();

                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse'=>$sms_response]);
                        } 
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$sms_response['url'],json_encode($initSms_json),$type,json_encode($sms_response['response']),$start_time, $end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                        if(isset($sms_response['response']) && $sms_response['response']['code']== "200"){
                            $response['success'] = true;
                            $flagData['initiator_sms'] = 1;
                            $response['sms_success'] = true;
                        }else{
                            $response['success'] = false;
                            $response['sms_success'] = false;
                            $response['sms_failure_msg'] = $sms_response['message'] ?? 'No response found';
                            Log::channel("response")->info(['Admin INIT SMS Exception' => 'No response found']);

                        }
                    } catch (\Throwable $th) {
                        Log::channel("response")->info(['Admin INIT SMS Exception' => $th->getMessage()]);
                        $response = [
                            'success' => false,
                            'message' =>  $th->getMessage(),
                        ]; 
                    }
                }else{
                    $response = [
                        'success' => false,
                        'message' => 'SMS sent already.',
                    ];
                }
                $TradebookBodyUpdateflags = $this->SpProcessor->updateFlagsInTradebookAdmin($flagData);
               
            }
            if($flag == 'acceptor'){
                $type = "RESPONDER ADMIN EMAIL";
                $flag = 'responder';
              
                $res_order_id = $tradeDetails->res_order_id;
              
                $res_path = DB::select('call sp_get_deal_slip_file_path_rfq(?, ?)', [$res_order_id, $trade_no]);
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['sp_get_deal_slip_file_path_rfq res'=>$res_path]);
                }
                if(!empty($res_path)){
                    $res_path = $res_path[0]->file_path;
                }else{
                    $response['success'] = false;
                    $response['message'] = 'Dealslip does not exist.';
                    return $response;
                }
                // defining initiator and responder details
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['call sp_get_userinfo_by_id_nse_rfq req'=>$res_user_id]);
                }
                $responder_user_details=DB::select("call bondbazaar.sp_get_userinfo_by_id_nse_rfq(".$res_user_id.")");
             
                $res_user_details = $responder_user_details[0];
                if(config('constant.FILE_LOG_REQUIRED')==true){
                    Log::channel("query")->info(['AdminEmailsmstriggerResponderUserDetails res'=>$res_user_details]);
                }
                if($tradeDetails->is_responder_email_sent =='0'){
                 
                    $res_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipEmailData($tradeDetails, $res_user_details, $res_path, $flag);
                    $res_json = json_decode($res_json, true);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerResponderMailResponse req'=>$res_json]);
                    } 
                    try {
                        $start_time = $this->getCurrentTimestampWithMicroseconds();
                        $res_mail_response = $this->sendEmail($res_json);
                        $end_time = $this->getCurrentTimestampWithMicroseconds();
                    
                        if(config('constant.FILE_LOG_REQUIRED')==true){
                            Log::channel("response")->info(['AdminEmailsmstriggerResponderMailResponse res'=>$res_mail_response]);
                        } 
                        $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$res_mail_response['url'],json_encode($res_json),$type,json_encode($res_mail_response['response']),$start_time,$end_time);
                        $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param); 
                                
                        if($res_mail_response['response']['code'] == "200"){
                            $flagData['responder_email'] = 1;
                            $response['success'] = true;
                            $response['email_success'] = true;
                            $ISResponderEmailsent = 1;
                        }else{
                            $response['success'] = false;
                            $response['email_success'] = false;
                            $response['email_failure_msg'] = $res_mail_response['message'] ?? 'No Response Found';
                            Log::channel("response")->info(['Admin RESP EMAIL Exception' => 'No response found']);

                        } 
                    } catch (\Throwable $th) {
                        Log::channel("response")->info(['Admin RESP EMAIL Exception' => $th->getMessage()]);
                        $response = [
                            'success' => false,
                            'message' =>  $th->getMessage(),
                        ];
                    }
                }else{
                    $response = [
                        'success' => false,
                        'message' => 'Mail sent already.',
                    ];
                }
                if($tradeDetails->is_responder_sms_sent=='0'){
                    $type = "RESPONDER ADMIN SMS";
                    $respSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($tradeDetails,$res_user_details,$flag);
                    $respSms_json = json_decode($respSms_json,true);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse req'=>$respSms_json]);
                    } 
                   try {
                    $start_time = $this->getCurrentTimestampWithMicroseconds();
                    $res_sms_response = $this->sendSMS($respSms_json);
                    $end_time = $this->getCurrentTimestampWithMicroseconds();

                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse res'=>$res_sms_response]);
                    } 
                    $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$res_sms_response['url'],json_encode($respSms_json),$type,json_encode($res_sms_response['response']),$start_time,$end_time);
                    $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);  
                                
                    if($res_sms_response['response']['code']== "200"){
                        $flagData['responder_sms'] = 1;
                        $response['success'] = true;
                        $response['sms_success'] = true;
                    }else{
                        $response['success'] = false;
                        $response['sms_success'] = false;
                        $response['sms_failure_msg'] = $res_sms_response['message'] ?? 'No response found.';
                        Log::channel("response")->info(['Admin RESP SMS Exception' => 'No response found.']);

                    } 
                   } catch (\Throwable $th) {
                    Log::channel("response")->info(['Admin INIT SMS Exception' => $th->getMessage()]);
                    $response = [
                        'success' => false,
                        'message' =>  $th->getMessage(),
                    ];
                    }
                }else{
                    $response = [
                        'success' => false,
                        'message' => 'SMS sent already.',
                    ];
                }
               
                $TradebookBodyUpdateflags = $this->SpProcessor->updateFlagsInTradebookAdmin($flagData);             
            }
            return response()->json($response);
        }catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function getCurrentTimestampWithMicroseconds() {
        $timestamp = microtime(true);
        return DateTime::createFromFormat('U.u', $timestamp)->format('Y-m-d H:i:s.u');
    }

    public function sendEmail($json_data) {
        $url = config("constant.notificationurl")."sendEmail";
        $post_data['headers'] = ['Content-Type: application/json'];
        $post_data['params'] = json_encode($json_data);
        $mail_response = $this->HttpProcessor->makeCurlRequest($post_data, $url);
        // return json_decode($mail_response['response'], true);
        return [
            'url' => $url,
            'response' => json_decode($mail_response['response'], true)
        ];
    }
    
    public function sendSMS($json_data) {
        $url = config("constant.notificationurl")."sendSMS";
        $sms_data['headers'] = ['Content-Type: application/json'];
        $sms_data['params'] = json_encode($json_data);
        $sms_response = $this->HttpProcessor->makeCurlRequest($sms_data, $url);
        // return json_decode($sms_response['response'], true);
        return [
            'url' => $url,
            'response' => json_decode($sms_response['response'], true)
        ];
    }
    // HANDLE DATA
    public function handleData($type,$api_response,$request,$secretmangerdata,$trade_id) {
       
        /*Below if is written so that that if password got changed then it should expire existigng token & call login again 
        For other errors noneed to call login api*/
        $resmsg='';
        if ($type=="addorder" && (isset($api_response['data']['RFQOrderResponceList'][0]['MESSAGE']))){
            $resmsg=$api_response['data']['RFQOrderResponceList'][0]['MESSAGE'];
        }
        if(empty($api_response['data']) || ($resmsg=="Invalid Password" || $resmsg=="Invalid Token")){ 
            $response = $this->UserController->setLoginTokenExpire($request['client_ip']);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['Rfq_handleDatatoken expireresp'=>$response]);
            }
            if($response['error_code'] != null){
                return $response;
            }
            $req_time=date('Y-m-d H:i:s');
            $headers = $this->ApiService->GenerateStandardHTTPHeader($type,$secretmangerdata,$response['token']);
            $data=[];
            if($type == "addorder"){
                $data = $this->GenerateApiRequestResponseBody->ReturnAddRfqRequestData($headers,$secretmangerdata,$request);
                // generate checksum value 
                $getchecksum = $this->generateCheckSum("AddRFQOrder",$secretmangerdata,$response['token'],json_encode($data['body']),$request['client_ip']);
                
                $data["headers"]["CHECKSUM"] =$getchecksum;
                
            }
            else {
                //acceptquote
                $data = $this->GenerateApiRequestResponseBody->ReturnRfqQuoteRequestData($headers,$secretmangerdata,$request);
                // generate checksum value 
                $getchecksum = $this->generateCheckSum("RFQQuoteAccept",$secretmangerdata,$response['token'],json_encode($data['body']),$request['client_ip']);
                 $data["headers"]["CHECKSUM"] =$getchecksum;
            }
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(["type"=>$type,'handledataapireqbody'=>$data]);
            }
            $ApiResponse = $this->HttpProcessor->InitHttpPostRequestProcessor($data);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['handleDatainsidecallresp'=>$ApiResponse]);
            }
            $res_time=date('Y-m-d H:i:s');
          
            if($type!="addorder"){
                $resmsginteral=$ApiResponse['data']['RFQQuoteAcceptResponceList'][0]['MESSAGE'];
                if($ApiResponse['data']['RFQQuoteAcceptResponceList'][0]['ERRORCODE'] !=0){
                    return $ApiResponse;
                }
            }else{
                $resmsginteral=$ApiResponse['data']['RFQOrderResponceList'][0]['MESSAGE'];
                if($ApiResponse['data']['RFQOrderResponceList'][0]['ERRORCODE'] !=0){
                    return $ApiResponse;
                }
            }

            //Save Logs
            $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($resmsginteral,$data,$ApiResponse,$secretmangerdata['data']['api_url'],"HandleDataAddRFQAccept",$trade_id,$req_time,$res_time,$request['client_ip']);
            $loginsertion=$this->SpProcessor->InsertAPILogs($logs);
          
            return $ApiResponse;
        }
        else{
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['handleDatainsidewithoutapihitresp'=>$api_response]);
            }
            return $api_response;
        }
    }


    public function getSecretManagerDetails($type) {
        $secret_manager_data = $this->AwsService->getSecret();
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['getSecretManagerDetails'=>$secret_manager_data]);
        }
        if($type=="acceptquote"){
            $secret_manager_data['data']['api_url'].="RFQQuoteAccept";
            return $secret_manager_data;

        }
        $secret_manager_data['data']['api_url'].="AddRFQOrder";
        return $secret_manager_data;
    }
//////////////////////////////////Admin validate///////////////////////////////////////////
    public function validateUser(Request $request){
        try {
            $username =  $request->username;
            $password =  $request->password;
            $tpa_code =  $request->tpa_code;
            $user_details=$this->SpProcessor->validateUserSp($username,$password,$tpa_code);  
            if(isset($user_details)){
                if($user_details[0]->ret_status == "1"){
                    return response()->json(['success'=>true, 'message'=>'Success' , 'userDetails'=> $user_details]);
                }else if($user_details[0]->ret_status == "0"){
                    return response()->json(['success'=>false, 'message'=>'Credential mismatch, Login failed']);
                }else if($user_details[0]->ret_status == "-1"){
                    return response()->json(['success'=>false, 'message'=>'TPA is Expired']);
                }else if($user_details[0]->ret_status == "-2"){
                    return response()->json(['success'=>false, 'message'=>'TPA is InActive']);
                }
            }else{
                return response()->json(['success'=>false, 'message'=>'Something went wrong!']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // public function money_format_conversion($number){
    //     $decimal = (string)($number - floor($number));
    //     $money = floor($number);
    //     $length = strlen($money);
    //     $delimiter = '';
    //     $money = strrev($money);

    //     for($i=0;$i<$length;$i++){
    //         if(( $i==3 || ($i>3 && ($i-1)%2==0) )&& $i!=$length){
    //             $delimiter .=',';
    //         }
    //         $delimiter .=$money[$i];
    //     }

    //     $result = strrev($delimiter);
    //     $decimal = preg_replace("/0\./i", ".", $decimal);
    //     $decimal = substr($decimal, 0, 3);

    //     if( $decimal != '0'){
    //         $result = $result.$decimal;
    //     }

    //     return $result;
    // }

     public function money_format_conversion($number){
        $no=(string)$number;
        $decimal=00;
        if (strpos($no, ".") !== false) {
            $decimal=explode(".",$no);
            $decimal=$decimal[1];
        }
        $money = floor($number);
        $length = strlen($money);
        $delimiter = '';
        $money = strrev($money);

        for($i=0;$i<$length;$i++){
            if(( $i==3 || ($i>3 && ($i-1)%2==0) )&& $i!=$length){
                $delimiter .=',';
            }
            $delimiter .=$money[$i];
        }

        $result = strrev($delimiter);
        // $decimal = preg_replace("/0\./i", ".", $decimal);
        // $decimal = substr($decimal, 0, 3);

        if( $decimal != '0'){
            $result = $result.".".$decimal;
        }

        return $result;
    }

    public function generateCheckSum($endpoint,$secretmangerdata,$token,$reqbody,$client_ip){
        $checksumheader = $this->ApiService->GenerateCheckSumHTTPHeader($endpoint,$secretmangerdata,$token);
        
        $getCheckSum=$this->getCheckSumValue($checksumheader,$reqbody);
        
        if($getCheckSum == 0){
            $response = $this->UserController->setLoginTokenExpire($client_ip);
            $token = $response["token"] ?? "";
            $checksumheader[2] = "TOKEN: ".$token;
            return $getCheckSum=$this->getCheckSumValue($checksumheader,$reqbody);
        }else{
            return $getCheckSum;
        }

    }

    public function getCheckSumValue($header,$reqbody){

        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(["checksumreqheader"=>$header,'checksumreqbody'=>$reqbody]);
        }

        $getchecksum = $this->HttpProcessor->makeCurlRequestCheckSum($header,$reqbody);

        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("response")->info(['checksumresponse'=>$getchecksum]);
        }

        if($getchecksum === "Invalid Token"){
            $getchecksum = 0;
        }

        return $getchecksum;    
    }


}