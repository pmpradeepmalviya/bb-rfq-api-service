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
            return $this->ApiService->SendHTTPErrorResponse(423,config('error.1017'));
        }else{
           $order_id= $NewInitiatorQuoteInsertion[0]->order_id;
            return ["msg"=>"New Quote Added Successfully","quote_id"=>$order_id];// no major returns so handled lik this 
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
        //return($request->all());
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
               // dd($AcceptQuoteResContent);
                //get the trade id from quote response & then do shilpi reporting 
                if (isset($AddRfqQuoteResponse[1]) && $AddRfqQuoteResponse[1]!=0){
                    $trade_no=$AddRfqQuoteResponse[1];
                    $shilpireporting = $this->ReportTradeToShilpi($trade_no,$request['client_ip']);
    
                    //dealslip trigger
                    // $trade_no=21;
                    $dealslipResponse = $this->GenerateDealSlip($trade_no);// get this from quote ka response as of now hardcode it 
                    // dd($dealslipResponse); 
                    $final_response=[
                        'AcceptQuoteOrderId'=>$AcceptQuoteResContent['data']['RFQQuoteAcceptResponceList'][0]['Ordernumber'],
                        'trade_no'=>$trade_no,
                    ];
                     //TO DO :here return final success later once deal book & slip work is done 
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
        // $api_response['data']['RFQOrderResponceList'][0]['MESSAGE']="Invalid Bank Details";
        // $api_response['data']['RFQOrderResponceList'][0]['ERRORCODE']=1;
        if((isset($api_response['data']['RFQOrderResponceList'][0]['ERRORCODE']))&& $api_response['data']['RFQOrderResponceList'][0]['MESSAGE']=="Invalid Token"){
            $response = $this->handleData("addorder",$api_response,$request,$secretmangerdata,$ResponderOrdbookinsertion[0]->trade_id);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['Rfqaddorders_handleDatacallerresp'=>$response]);
            }
             // $response['data']['RFQOrderResponceList'][0]['MESSAGE']="Invalid IFSC Details";
             // $response['data']['RFQOrderResponceList'][0]['ERRORCODE']=1;
            if((isset($response['data']['RFQOrderResponceList'][0]['ERRORCODE'])&& $response['data']['RFQOrderResponceList'][0]['ERRORCODE']!= 0) ||$response["error_code"]!=null){
                $system_error=config('error.1002');
                $error=$system_error." Reason- ".$response['data']['RFQOrderResponceList'][0]['MESSAGE'];

                //call sp to revert the qty & status 
                $childOrderid=$ResponderOrdbookinsertion[0]->order_id;
                $trade_id=$ResponderOrdbookinsertion[0]->trade_id;

                $RevertBody=$this->GenerateApiRequestResponseBody->CreateRevertBody($request,$childOrderid,$trade_id);
              //  dd($RevertBody,"qty");
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

        if(isset($RfqAddDbInsert["error"]) && $RfqAddDbInsert["error"]!='' || $RfqAddDbInsert[0]->ret_status != 0 ){
            return $this->ApiService->SendHTTPErrorResponse(424,config('error.1012'));
        }

        //Add RfqAddOrderResponse params need for hitting AcceptRfqQuote
        $request->request->add([
            'AcceptOrderDealID' => isset($response['data']['RFQOrderResponceList'][0]['RFQDealID'])?$response['data']['RFQOrderResponceList'][0]['RFQDealID']:null,
            'AcceptOrderNo' => isset($response['data']['RFQOrderResponceList'][0]['RFQOrdernumber'])?$response['data']['RFQOrderResponceList'][0]['RFQOrdernumber']:null,
            // 'TotalConsideration'=>isset($response['data']['RFQOrderResponceList'][0]['TotalConsideration'])?$response['data']['RFQOrderResponceList'][0]['TotalConsideration']:null,
            // 'ModAcrInt'=>isset($response['data']['RFQOrderResponceList'][0]['AccuredInterest'])?$response['data']['RFQOrderResponceList'][0]['AccuredInterest']:null,
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
        $logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($api_response['data']['RFQQuoteAcceptResponceList'][0]['MESSAGE'],$req,$api_response,$secretmangerdata['data']['api_url'],"AddRFQAccept",$request['trade_id'],$req_time,$res_time,$request['client_ip']);
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

        if(isset($RfqAcceptDbInsert["error"]) && $RfqAcceptDbInsert["error"]!='' || $RfqAcceptDbInsert[0]->ret_status != 0 ){
            return $this->ApiService->SendHTTPErrorResponse(425,config('error.1013'));
        }

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
        return [$this->ApiService->SendHTTPSuccessResponse($response),$request->trade_id];//need to return tradeid as its needed by deal slip and deal book
    }
    
    public function ReportTradeToShilpi($trade_no,$client_ip){

       // $trade_no=1;//as of now setting value hardcode
        $tradeDetails=$this->SpProcessor->getTradeDetails($trade_no);

        //dd($tradeDetails,"this");
        if($tradeDetails[0]->shilpi_reported_initiator==0 && $tradeDetails[0]->shilpi_reported_responder==0 ){
            
            $isShilpiReportedInitiator=0;
            $isShilpiReportedResponder=0;
    
            $initiatorShilpiRequest=$this->GenerateApiRequestResponseBody->CreateShilpiReqBody($tradeDetails[0],"initiator");
            $req_time_shilpi_initiator=date('Y-m-d H:i:s');

            $responderShilpiRequest=$this->GenerateApiRequestResponseBody->CreateShilpiReqBody($tradeDetails[0],"responder");
            $req_time_shilpi_responder=date('Y-m-d H:i:s');

            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['initiatorShilpiRequest'=>$initiatorShilpiRequest]);
                Log::channel("response")->info(['responderShilpiRequest'=>$responderShilpiRequest]);
            }
    
           //hit the api
            /*$api_response_initiator = $this->HttpProcessor->InitHttpGetRequestProcessor($initiatorShilpiRequest);
            $api_response_initiator =$api_response_buy->getBody()->getContents();
            $resp_time_shilpi_initiator=date('Y-m-d H:i:s');

            //write the validation code here to return error  as per the response of actual api hit 
            if($api_response_initiator)
           
            $api_response_responder= $this->HttpProcessor->InitHttpGetRequestProcessor($responderShilpiRequest);
            $api_response_responder=$api_response_sell->getBody()->getContents();
            $resp_time_shilpi_responder=date('Y-m-d H:i:s');

            $initiator_msg=isset($api_response_initiator['data']->status)?$api_response_initiator['data']->status:null;
            $responder_msg=isset($api_response_responder['data']->status)?$api_response_responder['data']->status:null;
    
            //initiator logs
            $initiator_logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($initiator_msg,$req,$api_response_initiator,$req['url'],"ShilpiInitiatorApi",$trade_no,$req_time_shilpi_initiator,$resp_time_shilpi_initiator,$client_ip);
            $initiator_loginsertion=$this->SpProcessor->InsertAPILogs($initiator_logs);
    
            //responder logs
            $responder_logs=$this->GenerateApiRequestResponseBody->CreateLogsBody($reponder_msg,$req,$api_response_responder,$req['url'],"ShilpiRespoderApi",$req_time_shilpi_responder,$resp_time_shilpi_responder,$client_ip);
            $responder_loginsertion=$this->SpProcessor->InsertAPILogs($responder_logs,$trade_no);
            */
    
             //Dummy 
            
            $api_response_initiator=['data'=>'{"Orderno":"100030","Tradeno":"100055","Status":"SUCCESS"}'];
            $api_response_responder=['data'=>'{"Orderno":"100031","Tradeno":"100055","Status":"SUCCESS"}'];
    
            if(!Str::contains($api_response_initiator['data'], 'ERROR')) {
                $isShilpiReportedInitiator=1;
            }
            if(!Str::contains($api_response_responder['data'], 'ERROR')) {
                $isShilpiReportedResponder=1;
            } 
            // dd($isShilpiReportedInitiator,">>>>",$isShilpiReportedResponder);
           
           //update in shilip tradebook
            $UpdateDealbookFlagsInitiator=$this->SpProcessor->UpdateDealbookFlagsShilpiTable($trade_no,$tradeDetails[0]->init_order_id,$isShilpiReportedInitiator);
            $UpdateDealbookFlagsResponder=$this->SpProcessor->UpdateDealbookFlagsShilpiTable($trade_no,$tradeDetails[0]->res_order_id,$isShilpiReportedResponder);
    
           //dd($UpdateDealbookFlagsInitiator,$UpdateDealbookFlagsResponder);
            if(config('constant.FILE_LOG_REQUIRED')==true){
               Log::channel("query")->info(['UpdateDealbookFlagsInitiator sp res'=>$UpdateDealbookFlagsInitiator]);
               Log::channel("query")->info(['UpdateDealbookFlagsInitiator sp res'=>$UpdateDealbookFlagsInitiator]);
            }
    
            $dbresinit=isset($UpdateDealbookFlagsInitiator[0]->ret_status)?$UpdateDealbookFlagsInitiator[0]->ret_status:null;
            $dbresresponder=isset($UpdateDealbookFlagsResponder[0]->ret_status)?$UpdateDealbookFlagsResponder[0]->ret_status:null;

            // commenting bekow becz sir told not to stop the flow if any of the dealbook hit isnt worked so we cant return if error comes 
            //if(is_null($dbresinit)){
            //     return $this->ApiService->SendHTTPErrorResponse(423,config('error.1005'));
            // }
            // if(is_null($dbresresponder)){
            //     return $this->ApiService->SendHTTPErrorResponse(423,config('error.1005'));
            // }
            return [$dbresinit,$dbresresponder];
        }
        else{
            return $this->ApiService->SendHTTPErrorResponse("429",config('error.1005'));
        }
    }

    public function GenerateDealSlip($trade_no){
         //$trade_no=11;
        //fetch the required data from db 
        $tradeDetails=$this->SpProcessor->getTradeDetails($trade_no);

        if($tradeDetails[0]->is_initiator_email_sent==0 && $tradeDetails[0]->is_responder_email_sent==0 
        && $tradeDetails[0]->is_initiator_sms_sent==0  && $tradeDetails[0]->is_responder_sms_sent==0 )
        {//we can exclude the sms flags as email is imp as its displayed in admin
            //Create initiator & reponder body
            $initiator_details=$this->GenerateApiRequestResponseBody->CreateDealSlipBody($tradeDetails,"initiator");
            $responder_details=$this->GenerateApiRequestResponseBody->CreateDealSlipBody($tradeDetails,"responder");

            $TodayDate=date('Ymd');
            // initiator_file upload
            $initiator_pdf = PDF::loadView('initiatordealslippdf', ['initiator_details'=>$initiator_details]);
            // $customPaper = array(0,0,800,1200);
            // $initiator_pdf->setPaper($customPaper);
            $init_content = $initiator_pdf->output();
            $init_filename="Init_Dealslip_".$initiator_details['order_no']."_".$initiator_details['trade_no'].'.pdf';
            $initiator_path = Storage::disk('s3')->put("RFQ/$TodayDate/$init_filename", $init_content);
            $initiator_path = Storage::disk('s3')->url($initiator_path);

            //responder _file upload
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
    
  public function emailSmsTrigger($trade_no ,$init_path,$res_path ) {
      try {
        $tradeDetails = $this->SpProcessor->getTradeDetails($trade_no)[0];
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
            $start_time = $this->getCurrentTimestampWithMicroseconds();
            $mail_response = $this->sendEmail($init_json);
            $end_time  = $this->getCurrentTimestampWithMicroseconds();
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerInitiatorMailResponse res'=>$mail_response]);
            } 
            $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$mail_response['url'],json_encode($init_json),$type,json_encode($mail_response['response']),$start_time, $end_time);
            $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
           
            if ($mail_response['response']['code'] == "200") {
                $response["ISInitiatorEmailsent"] = 1;
            } else {
                $response["ISInitiatorEmailSentFailure"] = $mail_response['message'];
            }
            // Send responder email
            $flag = 'responder';
            $res_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipEmailData($tradeDetails, $res_user_details, $res_path ,$flag);
            $res_json = json_decode($res_json,true);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerResponderMailResponse req'=>$res_json]);
            } 
            $start_time = $this->getCurrentTimestampWithMicroseconds();
            $res_mail_response = $this->sendEmail($res_json);
            $end_time = $this->getCurrentTimestampWithMicroseconds();
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerResponderMailResponse res'=>$res_mail_response]);
            }
            $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$res_mail_response['url'],json_encode($res_json),$type,json_encode($res_mail_response['response']),$start_time,$end_time);
            $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
          
            if ($res_mail_response['response']['code'] == "200") {
                $response["ISResponderEmailsent"] = 1;
            } else {
                $response["ISResponderEmailSentFailure"] = $res_mail_response['message'];
            }
        } else {
            $response["ISemailSentFailure"] = 'Email already sent';
        }
        // Check if SMSs are not already sent
        if ($tradeDetails->is_initiator_sms_sent == '0' && $tradeDetails->is_responder_sms_sent == '0') {
            $type = 'SMS';
            // Send initiator SMS
            $initSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($init_user_details);
            $initSms_json = json_decode($initSms_json,true);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerInitiatorMSMSResponseSpResponse req'=>$initSms_json]);
            } 
            $start_time = $this->getCurrentTimestampWithMicroseconds();

            $res_mail_response = $this->sendEmail($res_json);
            $sms_response = $this->sendSMS($initSms_json);
            $end_time = $this->getCurrentTimestampWithMicroseconds();


            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerInitiatorMSMSResponseSpResponse res'=>$sms_response]);
            } 
            $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$sms_response['url'],json_encode($initSms_json),$type,json_encode($sms_response['response']),$start_time,$end_time);
            $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
        
            if ($sms_response['response']['code'] == "200") {
                $response["ISInitiatorSMSsent"] = 1;
            } else {
                $response["ISInitiatorSmsSentFailure"] = $sms_response['message'];
            }
    
            // Send responder SMS
            $respSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($res_user_details);
            $respSms_json = json_decode($respSms_json,true);
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerResponderSMSResponse req'=>$respSms_json]);
            }
            $start_time = $this->getCurrentTimestampWithMicroseconds();
            $res_sms_response = $this->sendSMS($respSms_json);
            $end_time = $this->getCurrentTimestampWithMicroseconds();

            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("response")->info(['emailsmstriggerResponderSMSResponse res'=>$res_sms_response]);
            }
            $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$res_sms_response['url'],json_encode($respSms_json),$type,json_encode($res_sms_response['response']),$start_time,$end_time);
            $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
         
            if ($res_sms_response['response']['code'] == "200") {
                $response["ISResponderSmsSent"] = 1;
            } else {
                $response["ISResponderSmsSentFailure"] = $res_sms_response['message'];
            }
        } else {
            $response["ISsmsSentFailure"] = 'SMS already sent';
        }
    
        return $response;
      } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => $th->getMessage()
        ]);
      }
    }

    public function adminEmailSmsTrigger(Request $request){
       try {
            $response = [
                'success' => false,
            ];
            $trade_no = $request->trade_id;
            $flag = $request->flag;
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
                    $start_time = $this->getCurrentTimestampWithMicroseconds();
                    $mail_response = $this->sendEmail($init_json);
                    $end_time = $this->getCurrentTimestampWithMicroseconds();
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMailResponse res'=>$mail_response]);
                    } 
                    $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$mail_response['url'],json_encode($init_json),$type,json_encode($mail_response['response']),$start_time,$end_time);
                    $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                    
                    if($mail_response['response']['code'] == "200"){
                        $response['success'] = true;
                        $flagData['initiator_email'] = 1;
                        $response['email_success'] = true;
                    }else{
                        $response['success'] = false;
                        $response['email_failure_msg'] =$mail_response['message'];
                        $response['email_success'] = false;
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
                    $initSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($init_user_details);
                    $initSms_json = json_decode($initSms_json,true);
                    // Call common SMS service
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse req'=>$initSms_json]);
                    } 
                    $start_time = $this->getCurrentTimestampWithMicroseconds();
                    $sms_response = $this->sendSMS($initSms_json);
                    $end_time = $this->getCurrentTimestampWithMicroseconds();

                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse'=>$sms_response]);
                    } 
                    $sp_log_param=$this->GenerateApiRequestResponseBody->returnSpLogData($trade_no,$sms_response['url'],json_encode($initSms_json),$type,json_encode($sms_response['response']),$start_time, $end_time);
                    $notifLoginsertion=$this->SpProcessor->notificationLogs($sp_log_param);
                    if($sms_response['response']['code']== "200"){
                        $response['success'] = true;
                        $flagData['initiator_sms'] = 1;
                        $response['sms_success'] = true;
                    }else{
                        $response['success'] = false;
                        $response['sms_success'] = false;
                        $response['sms_failure_msg'] = $sms_response['message'];
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
                        $response['email_failure_msg'] = $res_mail_response['message'];
                        $ISResponderEmailSentFailure =$res_mail_response['message'];
                    } 
                }else{
                    $response = [
                        'success' => false,
                        'message' => 'Mail sent already.',
                    ];
                }
                if($tradeDetails->is_responder_sms_sent=='0'){
                    $type = "RESPONDER ADMIN SMS";
                    $respSms_json = $this->GenerateApiRequestResponseBody->ReturnDealSlipSmsData($res_user_details);
                    $respSms_json = json_decode($respSms_json,true);
                    if(config('constant.FILE_LOG_REQUIRED')==true){
                        Log::channel("response")->info(['AdminEmailsmstriggerInitiatorMSMSResponseSpResponse req'=>$respSms_json]);
                    } 
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
                        $response['sms_failure_msg'] = $res_sms_response['message'];
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
        $url = 'https://uat.bondbazaar.com/alerts/api/sendEmail';
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
        $url = 'https://uat.bondbazaar.com/alerts/api/sendSMS';
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
            $response = $this->UserController->setLoginTokenExpire();
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
            }
            else {
                //acceptquote
                $data = $this->GenerateApiRequestResponseBody->ReturnRfqQuoteRequestData($headers,$secretmangerdata,$request);
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
}
