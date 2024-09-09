<?php

namespace App\Http\Request\api;

class GenerateApiRequestResponseBody {

    // LOGIN API DATA
    public function CreateloginReqBody($request,$headers){
        // INITIALIZE A REQUEST
        $url = $request['api_url'] . 'GenerateToken';
        $data = [
            'api_name'=> 'RFQLoginApi',
            'request_name' => 'RFQLogin',
            'method_name' => 'GET',
            'headers' =>$headers,
            'url' => $url,
        ];
        return $data;
    }

    // CHANGE PASSWORD DATA
    public function changepassworddata($secret_data,$new_password){
        // INITIALIZE A REQUEST
        $url = $secret_data['api_url'] . '/v1/changePassword';
        $data = [
            'api_name'=> 'ChangePasswordApi',
            'request_name' => 'ChangePassword',
            'method_name' => 'POST',
            'headers' => '',
            'url' => $url,
            'body' => array(
                'loginId'=> $secret_data['loginId'],
                'member'=> $secret_data['member'],
                'password'=> $secret_data['password'],
                'newPassword' => $new_password,
            ),
            'request_time'=> date('Y-m-d H:i:s'),
        ];
        return $data;
    }

    // GENERATE ADD ORDER DATA
    public function ReturnAddRfqRequestData($headers,$details,$request){
        // $price=($request->clean_price*100)/$request->dirty_price; // as per akshya's formula on email
  
         $data = [
             'api_name'=> 'ReturnAddRfqRequestDataApi',
             'request_name' => 'ReturnAddRfqRequestData',
             'method_name' => 'POST',
             'headers' => $headers,
             'url' => $details['data']['api_url'],
             'body' => [
                 'RFQORDERDETAILS' => [//creating array as per api mappings excel file
                     [
                     "BondType" => $request->BondCategory,
                     "DealType" => "OBP",
                     "BidOffer" => $request->BidOfferInitiator,
                     "ISINNumber" => $request->ISINNumber,
                     "Rating" => $request->Rating,
                     "RatingAgency" => $request->RatingAgency,
                     "Value" => $request->Value,// this will be facevalue*qty
                     "MinimumOrderValue" => $request->MinimumOrderValue,//this will be facevalue
                     "TypeOfYield" => "YTM",
                     "Yield" => $request->Yield,
                     "Price" => $request->Price,//this is as per akshya's formula
                     "SettlementType" => "1",
                     "GFD" => "0",
                     "DealTimeHours" => "",
                     "DealTimeMinutes" => "",
                     "InitiatorParticipantLoginID" => "BSPL",//"BONDBSPL",
                     "InitiatorDealerLoginID" => "BSPLD",//"BBSPL01",
                     "OTOOTM" =>"OTO",
                     "OTOParticipantName" =>"BSPL",//"BONDBSPL",// as per akshya's email: Offer - Bid API Testing Successful
                     "ProClient" => $request->ProClientInitiator, //"CLIENT",- 29th april 2024
                     "InitiatorName" => "BSPL",//"BONDBSPL",
                     "BuyerClientName" => $request->BuyerClientName,//initiator_ucc
                     "SellerClientName" => $request->SellerClientName,//responder_ucc
                     "DirectBrokered" => ($request->ProClientInitiator=='PRO')?"DIRECT":"BROKERED",
                     "SellerBrokerName" => "BSPL",//"BONDBSPL",
                     "BuyerBrokerName" => "BSPL",//"BONDBSPL",
                     "NegotiableFlag" => "NO",
                     "DisclosedIdentity" => "NO",
                     "InitiatorCustodian" => "",
                     "InitiatorIFSC" => $request->InitiatorIFSC,
                     "InitiatorBankAccountNumber" => $request->InitiatorBankAccountNumber,
                     "InitiatorDpType" => $request->InitiatorDpType,
                     "InitiatorDpId" => $request->InitiatorDpId,
                     "InitiatorClientID" => $request->InitiatorClientID,
                     "InitiatorReferenceNumber" =>"",
                     "InitiatorComment" => "",
                     "InternalOrderNumber" => $request->InternalOrderNumber,
                     ]
                    
                 ],
             ],
         ];
         return $data;
     }

    //GENERATE QUOTE ACCEPT DATA

    public function ReturnRfqQuoteRequestData($headers,$details,$request){
        $data = [
            'api_name'=> 'ReturnRfqQuoteRequestData',
            'request_name' => 'ReturnRfqQuoteRequestData',
            'method_name' => 'POST',
            'headers' => $headers,
            'url' => $details['data']['api_url'],
            'body' => [
                'RFQQuoteAccept' => [//creating array as per api mappings excel file
                    [
                    "BondType" => $request->BondCategory,
                    "DealType" => "OBP",
                    "BidOffer" => $request->BidOfferResponder,
                    "ISINNumber" => $request->ISINNumber,
                    "RFQOrderNumber" => $request->AcceptOrderNo,
                    "ResponderParticipantLoginID"=>"BSPL",//"BONDBSPL",
                    "ResponderDealerLoginID"=>"BSPLD",//"BBSPL01",
                    "ModAcrInt"=>$request->accrued_int,
                    "TotalConsideration"=>$request->total_consideration_popup,
                    "ProClient"=>$request->ProClientResponder,//"CLIENT", 29th april
                    "BuyerClientName"=>$request->BuyerClientName,
                    "SellerClientName"=>$request->SellerClientName,
                    "DirectBrokered"=> ($request->ProClientResponder=='PRO')?"DIRECT":"BROKERED",//"BROKERED",
                    "SellerBrokerName"=>"BSPL",//"BONDBSPL",
                    "BuyerBrokerName"=>"BSPL",//"BONDBSPL",
                    "ResponderName"=>"BSPL",//"BONDBSPL",//as per excel $request->user_ucc,
                    "ResponderCustodian"=>"",
                    "ResponderBankIFSC"=>$request->ResponderBankIFSC,
                    "ResponderAccountNumber"=>$request->ResponderAccountNumber,
                    "ResponderDpType"=>$request->ResponderDpType,
                    "ResponderDpId"=>$request->ResponderDpId,
                    "ResponderDpClientID"=>$request->ResponderDpClientID,
                    "ResponderReferanceNumber"=>"",
                    "ResponderComment"=>"",
                    "InternalOrderNumber"=>$request->InternalOrderNumber,
                    "RFQDealID"=>$request->AcceptOrderDealID,
                    ],
                   
                ],
            ],
        ];
        return $data;
    }

    public function CreateResponderOrdbookEntryBody($body){
        if($body->BidOfferResponder=='OFFER'){
           $order_action='Sell';
        }else{
            $order_action='Buy';
        }

       return [
       "order_id" =>0,// jayesh told to pass null
        "user_id" =>$body->user_id,
        "order_dtm"=>date('Y-m-d H:i:s'),
        "user_ucc_no"=>$body->user_ucc,
        "order_type"=>$body->order_type,// this can be basket- B or single- S
        "basket_category_id"=>$body->basket_category_id,
        "basket_category_name"=>$body->basket_category_name,
        "basket_id"=> $body->basket_id ,
        "basket_name"=>$body->basket_name,
        "basket_display_name"=>$body->basket_display_name,
        "basket_qty"=>$body->basket_qty,
        "tpa_id"=>$body->tpa_id, 
        "tpa_name"=>"",// need to decide what to pass , yes still discussions are in process
        "isin"=>$body->ISINNumber,
        "issuer_name"=>$body->issuer_name,
        "order_action"=>$order_action,
        "order_qty"=>$body->order_qty,
        "executed_qty"=>$body->executed_qty,
        "pending_qty"=>$body->pending_qty,
        "category_id"=>$body->category_id,
        "yield"=>$body->Yield,
        "clean_price"=>$body->clean_price,
        "accrued_int"=>$body->accrued_int,
        "dirty_price"=>$body->dirty_price,
        "total_consideration"=>$body->total_consideration_popup,
        "order_status_id"=>1,
        "order_status"=>"Pending",
        "parent_order_no"=>$body->InternalOrderNumber,
        "weighted_avg_yield"=>$body->weighted_avg_yield,
        "total_basket_price"=>$body->total_basket_price,
        "bo_id"=>$body->bo_id,
        "bank_acc_no"=>$body->ResponderAccountNumber,
        "uploaded_by"=>$body->user_ucc,
        "created_by"=>$body->user_ucc,
        //check these with jayesh once , check sp processor also for params order
        "brokerage_percentage"=> $body->brokerage_percentage,
        "brokerage_amount"=>$body->brokerage_amount,
        "stamp_duty"=>$body->stamp_duty,
        "settlement_amount"=>$body->settlement_amount,
        "tpa_unique_id"=>NULL,
        "c2t_user_id"=>0,
        "SettlementType" =>(isset($body->SettlementType) && $body->SettlementType != '') ? $body->SettlementType:Null,
       ];
    }
    //RFQ Logging
    public function CreateLogsBody($msg,$req=null,$res=null,$url,$name=null,$trade_id,$request_time=null,$response_time=null,$client_ip=null){
        $logBody=array(
            'api_error_msg'=>$msg,
            'api_status_code'=>'',
            'api_request_ip'=>$client_ip,
            'api_request_header'=>'',
            'api_response_msg'=>json_encode($res),
            'api_request_msg'=>json_encode($req),
            'api_url'=>$url,
            'api_internal_url_name'=>$name,
            'trade_id'=>$trade_id,
            'api_request_start_dtm'=>$request_time,
            'api_request_end_dtm'=>$response_time,
        );
        return $logBody;
    }

    public function CreateAPIInsertBody($type,$request,$response){
        //dd($response,">>>",$request->all());
        if($response['OrderStatus']==1){
            $order_status="Deal Pending";
        }else if ($response['OrderStatus']==7){
            $order_status="Deal Confirmed";
        }else if ($response['OrderStatus']==8){
            $order_status="Deal Cancelled";
        }else{
            $order_status=$response['OrderStatus'];
        }
        if ($type =="AddRFQOrder"){
            return [
                "order_id" =>0,
                "user_id"=>$request->user_id,
                "user_ucc"=>$request->user_ucc,
                "internal_order_no"=>$request->InternalOrderNumber,
                "trade_id"=>0,//not needed as we have parent order id to track
                "BondType" => $request->BondCategory,
                "DealType" => "OBP",
                "BidOfferInitiator" => $request->BidOfferInitiator,
                "ISINNumber" => $request->ISINNumber,
                "Rating" => $request->Rating,
                "RatingAgency" => $request->RatingAgency,
                "Value" => $request->Value,
                "MinimumOrderValue" => $request->Value,
                "TypeOfYield" => "YTM",
                "Yield" => $request->Yield,
                "Price" => $request->Price,
                "SettlementType" => "1",
                "GFD" => 0,
                "DealTimeHours" => "",
                "DealTimeMinutes" => "",
                "InitiatorParticipantLoginID" => "BONDBSPL",
                "InitiatorDealerLoginID" => "BBSPL01",
                "OTOOTM" =>"OTO",
                "OTOParticipantName" =>$request->OTOParticipantName,
                "ProClient" => "CLIENT",
                "InitiatorName" => "BONDBSPL",
                "BuyerClientName" => $request->BuyerClientName,
                "SellerClientName" => $request->SellerClientName,
                "DirectBrokered" => "BROKERED",
                "SellerBrokerName" => "BONDBSPL",
                "BuyerBrokerName" => "BONDBSPL",
                "NegotiableFlag" => "NO",
                "DisclosedIdentity" => "NO",
                "InitiatorCustodian" => "",
                "InitiatorIFSC" => $request->InitiatorIFSC,
                "InitiatorBankAccountNumber" => $request->InitiatorBankAccountNumber,
                "InitiatorDpId" => $request->InitiatorDpId,
                "InitiatorDpType" => "CDSL",
                "InitiatorClientID" => $request->InitiatorClientID,
                "InitiatorReferenceNumber" =>"",
                "InitiatorComment" => "",
                "Res_Errorcode"=>$response['ERRORCODE'],
                "Res_Message"=>$response['MESSAGE'],
                "Res_RFQOrdernumber"=>$response['RFQOrdernumber'],
                "Res_ISINNumber"=>$response['ISINNumber'],
                "Res_amount"=>$response['TotalConsideration'],
                "Res_MinRespAmount"=>$response['MinRespValue'],
                "Res_Yield"=>$response['Yield'],
                "Res_dealID"=>$response['RFQDealID'],
                "Res_dealtime"=>$response['Dealtime'],
                "Res_AccuredInterest"=>$response['AccuredInterest'],
                "Res_Ordrstatus"=>$order_status,
                "Res_Price"=>$response['Price'],
                "Res_Value"=>$response['Value'],                
            ];
        }else{

            return [
                "order_id"=>0,
                "user_id"=>$request->user_id,
                "user_ucc"=>$request->user_ucc,
                "internal_order_no"=>$request->InternalOrderNumber,
                "trade_id"=>$request->trade_id,//not needed as you have parent order id 
                "BondType" => $request->BondCategory,
                "DealType" => "OBP",
                "BidOfferResponder" => $request->BidOfferResponder,
                "ISINNumber" => $request->ISINNumber,
                "RFQaddOrderNumber" => $request->AcceptOrderNo,//this is add order resp values
                "RFQaddOrderdealid" => $request->AcceptOrderDealID,//this is add order resp values
                "ResponderParticipantLoginID"=>"BONDBSPL",
                "ResponderDealerLoginID"=>"BBSPL01",
                "ModAcrInt"=>$request->accrued_int,
                "TotalConsideration"=>$request->total_consideration_popup,
                "ProClient"=>"CLIENT",
                "BuyerClientName"=>$request->BuyerClientName,
                "SellerClientName"=>$request->SellerClientName,
                "DirectBrokered"=> "BROKERED",
                "SellerBrokerName"=>"BONDBSPL",
                "BuyerBrokerName"=>"BONDBSPL",
                "ResponderName"=>"BONDBSPL",
                "ResponderCustodian"=>"",
                "ResponderBankIFSC"=>$request->ResponderBankIFSC,
                "ResponderAccountNumber"=>$request->ResponderAccountNumber,
                "ResponderDpType"=>"CDSL",
                "ResponderDpId"=>$request->ResponderDpId,
                "ResponderDpClientID"=>$request->ResponderDpClientID,
                "ResponderReferanceNumber"=>"",
                "ResponderComment"=>"",
                "Res_Errorcode"=>$response['ERRORCODE'],
                "Res_Message"=>$response['MESSAGE'],
                "Res_RFQOrdernumber"=>$response['Ordernumber'],
                "Res_ISINNumber"=>$response['ISINNumber'],
                "Res_amount"=>$response['TotalConsideration'],
                "Res_Yield"=>$response['Yield'],
                "Res_dealID"=>$response['RFQDealID'],
                "Res_dealtime"=>$response['Dealtime'],
                "Res_AccuredInterest"=>$response['AccuredInterest'],
                "Res_Ordrstatus"=>$order_status,
                "Res_Price"=>$response['Price'],
                "Res_Value"=>$response['Value'],     
            ];
        }
    }

    public function CreateShilpiReqBody($request){
    // INITIALIZE A REQUEST
        
        $shilpiurl = "";//config("constant.SHLIPIURL");//PROD_SHLIPIURL
        $request=(json_decode(json_encode($request),true));
        $benid=$request[0]['benefeciary_id'];
       // $stamp_duty=(strtolower($request[0]['deal_type'])=="sell"?0.00:$request[0]['stampdutyseller']);
        $principle_amount=($request[0]['amortised_facevalue']=="" ||$request[0]['amortised_facevalue']==NULL || $request[0]['amortised_facevalue']==0)?$request[0]['face_value']:$request[0]['amortised_facevalue'];
     //   return $request[0]['issuer_nm'];
        // $total_consideration_without_stampduty=$request[0]['total_consideration'];
        // $total_consideration_with_stampduty=$request[0]['total_consideration'];
        // if ($request[0]['total_consideration']>500000.00){
        //     $total_consideration_without_stampduty=$request[0]['total_consideration']-$stamp_duty;
        //     $total_consideration_with_stampduty=$request[0]['total_consideration']+$stamp_duty;
        // }
     //   $rate_of_stampduty=(strtolower($request[0]['deal_type'])=="sell"?0.00:0.0001);


        $request[0]['deal_type']=strtolower($request[0]['deal_type'])=="buy"?"B":"S";

        $xmlbody=$shilpiurl.'<?xml version="1.0" encoding="utf-8"?><transactiondetails> <dealbook> <orderdate>'.$request[0]['orderdate'].'</orderdate><ordertime>'.$request[0]['ordertime'].'</ordertime> <orderno>'.$request[0]['order_id'].'</orderno> <tradedate>'.$request[0]['trade_date'].'</tradedate> <tradetime>'.$request[0]['trade_time'].'</tradetime> <tradeno>'.$request[0]['trade_id'].'</tradeno> <dealid></dealid> <ucccode>'.$request[0]['ucc_code'].'</ucccode> <participantname>'.$request[0]['participant_name'].'</participantname> <isin>'.$request[0]['isin'].'</isin> <securityname>'.$request[0]['issuer_nm'].'</securityname> <dealtype>'.$request[0]['deal_type'].'</dealtype> <quantity>'.$request[0]['quantity'].'</quantity> <facevalue>'.$request[0]['face_value'].'</facevalue> <couponrate>'.$request[0]['coupon_rate'].'</couponrate> <maturitydate>'.$request[0]['maturity_date'].'</maturitydate> <cleanprice>'.$request[0]['clean_price'].'</cleanprice> <accruedinterest></accruedinterest> <modifiedaccruedinterest>'.$request[0]['accrued_interest'].'</modifiedaccruedinterest> <dirtyprice>'.$request[0]['dirty_price'].'</dirtyprice> <totalconsiderationbefore>'.$request[0]['total_consideration'].'</totalconsiderationbefore> <rateofstampduty>'.$request[0]['rate_of_stampduty'].'</rateofstampduty> <stampdutyseller>'.$request[0]['stampdutyseller'].'</stampdutyseller> <totalconsiderationafter>'.$request[0]['total_considerationafter_stamp_duty'].'</totalconsiderationafter> <yieldtype>YTM</yieldtype> <yield>'.$request[0]['yield'].'</yield> <dealtby>Head Office</dealtby> <nameofbroker></nameofbroker> <settlementschedule>T1</settlementschedule> <settlementdate>'.$request[0]['settlement_date'].'</settlementdate> <settlementno>'.$request[0]['settlement_no'].'</settlementno> <dealerid></dealerid> <dealername></dealername> <bankname>'.$request[0]['bank_name'].'</bankname> <bankaccountno>'.$request[0]['bank_account_no'].'</bankaccountno><ifsccode>'.$request[0]['ifsc_code'].'</ifsccode> <dpname>'.$request[0]['dp_name'].'</dpname> <dptype>CDSL</dptype> <dpid>'.$request[0]['dp_id'].'</dpid> <benefeciaryid>'.$benid.'</benefeciaryid> <intenttosettle>Yes</intenttosettle> <reportingon>NSDRST</reportingon> <settlement>Unsettled</settlement> <producttype>'.$request[0]['product_type'].'</producttype> <ndrstordernumber>'.$request[0]['order_id'].'</ndrstordernumber> <couponfrequency></couponfrequency> <pricipalamount>'.$principle_amount.'</pricipalamount> <redemptiondate></redemptiondate> <redemptionamount></redemptionamount> <calldate></calldate> <putdate></putdate> <settlementdate></settlementdate> </dealbook> </transactiondetails>';
       // return $xmlbody;
      //  dd($xmlbody);
        $data = [
            'api_name'=> 'ShilpiReporting',
            'request_name' => 'ShilpiReporting',
            'method_name' => 'GET',
            'headers' =>["Content-Type"=>"application/xml"],
            'url' => $xmlbody,
        ];
        return $data;
    }

    public function CreateDealSlipBody($dealslipDetails,$type,$tradeDetails){
        if ($type=="initiator"){
            $stamp_duty=strtolower($dealslipDetails[0]->deal_type)=='sell'?0.00:$dealslipDetails[0]->stampdutyseller;
             return [ 
                "name"=>$tradeDetails[0]->init_user_name,
                "ucc"=>$tradeDetails[0]->init_user_ucc,
                "order_type"=>$tradeDetails[0]->init_order_action,
                "order_no"=>$tradeDetails[0]->quote_ordernumber,//accept_order_rfq_deal_id,// told by akshya while discussing shilpi params,
                "trade_no"=>$tradeDetails[0]->trade_id,
                "order_dtm"=>$tradeDetails[0]->init_order_dtm,
                "trade_dtm"=>$tradeDetails[0]->trade_dtm,
                "isin"=>$tradeDetails[0]->isin,
                "face_value"=>round($tradeDetails[0]->face_value,2),//need to confirm  mapping
                "yield"=>$tradeDetails[0]->yield,
                "coupon_rate"=>$tradeDetails[0]->coupon_rate,//asked db team to add column once done will chnge
                "maturity_date"=>$tradeDetails[0]->maturity_date,//asked db team to add column once done will chnge
                "security_name"=>$tradeDetails[0]->issuer_name,
                "clean_price"=>$tradeDetails[0]->clean_price,
                "accrued_interest"=>$tradeDetails[0]->accrued_int,
                "dirty_price"=>$tradeDetails[0]->dirty_price,
                "stamp_duty"=>$stamp_duty,//told by akshya while discussing shilpi params
                "quantity"=>$tradeDetails[0]->init_order_qty,
                "total_consideration"=>$tradeDetails[0]->traded_value,
                "settlement_no"=>$tradeDetails[0]->settlement_no,
                "settlement_date"=>$tradeDetails[0]->settlement_date,
                "settlement_type"=>$tradeDetails[0]->settlement_type,
                "settlement_amount"=>$tradeDetails[0]->settlement_amount+$stamp_duty,
                "dpid"=>$tradeDetails[0]->init_dp_id,
            ];
           
           
        }else{
           $stamp_duty=strtolower($dealslipDetails[0]->deal_type)=='sell'?0.00:$dealslipDetails[0]->stampdutyseller;
            return [ 
                "name"=>$tradeDetails[0]->res_user_name,
                "ucc"=>$tradeDetails[0]->res_user_ucc,
                "order_type"=>$tradeDetails[0]->res_order_action,
                "order_no"=>$tradeDetails[0]->quote_ordernumber,//told by aksaya while discussing about shilpi params,
                "trade_no"=>$tradeDetails[0]->trade_id,
                "order_dtm"=>$tradeDetails[0]->res_order_dtm,
                "trade_dtm"=>$tradeDetails[0]->trade_dtm,
                "isin"=>$tradeDetails[0]->isin,
                "face_value"=>round($tradeDetails[0]->face_value,2),//need to confirm  mapping
                "yield"=>$tradeDetails[0]->yield,
                "coupon_rate"=>$tradeDetails[0]->coupon_rate,//asked db team to add column once done will chnge
                "maturity_date"=>$tradeDetails[0]->maturity_date,//asked db team to add column once done will chnge
                "security_name"=>$tradeDetails[0]->issuer_name,
                "clean_price"=>$tradeDetails[0]->clean_price,
                "accrued_interest"=>$tradeDetails[0]->accrued_int,
                "dirty_price"=>$tradeDetails[0]->dirty_price,
                "stamp_duty"=>$stamp_duty,//told by akshya while discussing shilpi params,
                "quantity"=>$tradeDetails[0]->res_order_qty,
                "total_consideration"=>$tradeDetails[0]->traded_value,
                "settlement_no"=>$tradeDetails[0]->settlement_no,
                "settlement_date"=>$tradeDetails[0]->settlement_date,
                "settlement_type"=>$tradeDetails[0]->settlement_type,
                "settlement_amount"=>$tradeDetails[0]->settlement_amount+$stamp_duty,
                "dpid"=>$tradeDetails[0]->res_dp_id,
            ]; 
        }
    }

     // CreateNewInitiatorQuoteBody
    public function CreateNewInitiatorQuoteBody($body){
       // dd($body->settlement_amount);
       return [
        "order_id" =>0,
        "user_id" =>$body->user_id,
        "order_dtm"=>date('Y-m-d H:i:s'),
        "user_ucc_no"=>$body->user_ucc,
        "order_type"=>"S",
        "tpa_id"=>$body->tpa_id, 
        "tpa_code"=>$body->tpa_code, 
        "tpa_name"=>$body->tpa_name,// need to decide what to pass , yes still discussions are in process
        "isin"=>$body->ISINNumber,
        "issuer_name"=>$body->issuer_name,
        "order_action"=>$body->QuoteType,
        "order_qty"=>$body->order_qty,
        "category_id"=>$body->category_id,
        "yield"=>$body->Yield,
        "clean_price"=>$body->clean_price,
        "accrued_int"=>$body->accrued_int,
        "dirty_price"=>$body->dirty_price,
        "total_consideration"=>$body->total_consideration,
        "order_status_id"=>1,
        "order_status"=>"Pending",
        "bo_id"=>$body->dp_id,
        "bank_acc_no"=>$body->ResponderAccountNumber,
        "uploaded_by"=>$body->user_ucc,
        "created_by"=>$body->user_ucc,
        "brokerage_percentage"=> $body->brokerage_percentage,
        "brokerage_amount"=>$body->brokerage_amount,
        "stamp_duty"=>$body->stamp_duty,
        "settlement_type"=>isset($body->settlement_type) ? $body->settlement_type:NULL,
        "c2t_user_id"=>isset($body->c2t_user_id) ? $body->c2t_user_id:0,
       ];
    }

    public function CreateRevertBody($req,$childOrderId,$trade_id){
        return $paymentbody=[
            "user_id"=>$req->user_id,//current logged in user
            "order_id"=>$childOrderId,
            "order_status_id"=>8,
            "order_status"=>"Failed",
            "order_qty"=>$req->order_qty,// as frontend is sending the responder's qty  in this field   
            "parent_order_id"=>$req->InternalOrderNumber,
            "trade_id"=>$trade_id,
            "trade_status"=>"Failed" 
        ];
    }
    //////////////////////payments///////////////////////

    public function CreatePaymentTokenReqBody($request){

        $data = [
            'api_name'=> 'PayphiPaymentAPI',
            'request_name' => 'PayphiPaymentAPI',
            'method_name' => 'POST',
            'headers' =>[],
            'url' => $request['pay_api_url'] . 'token',
            'body'=>[
                "username"=>$request["pay_username"],
                "password"=>$request["pay_password"],
                "grant_type"=>$request["pay_grant_type"],
            ]
        ];
        return $data;
    }
     //Generatepaymenlinkrequest body
    public function ReturnPaymentRequestBody($encryptedBody,$token){
       
        $data = [
            'api_name'=> 'GeneratePaymetLink',
            'request_name' => 'GeneratePaymentLink',
            'method_name' => 'POST',
            'token' => $token,
            'url' => "https://uat-rfqepay.bseindia.com/api/SendPaymentLink",
            'params' => $encryptedBody,
            
        ];
        return $data;
    }

    public function CreateTxnReqBody($req,$payment_link){
        return $paymentbody=[
       "order_no"=>$req->order_no,
       "trade_no"=>$req->trade_no,
       "payment_link"=>$payment_link,     
       "link_status"=>"Created",//As of now will descide the logic later with sir  
       "user_id"=>$req->user_id,//current logged in user
        ];
    }

    public function ReturnEncApiBody($ReturnEncApiBody){
       
        $data = [
            'api_name'=> 'GetEncryptedData',
            'request_name' => 'GetEncryptedData',
            'method_name' => 'POST',
            'headers' => ['Content-Type' => 'application/json','ApiKey' => 'B0pytu9zfptgswf4YRjmqfpCpyEKACfy='],
            'url' => "http://43.205.5.14/rfq/api/Crypto/EncryptText",
            'body' => ["InputText"=>$ReturnEncApiBody,"EncKey"=> "017345ZA28ABAAEFGHIJKLMNOZZRSTUVWX67"],
        ];
        return $data;
    }

    //==================== For Report Body ==================================

    public function CreateOrderReportBody($request){
        return [
            "user_id"=>$request->user_id,
            "order_type"=>$request->order_toggle,
            "category_id"=>$request->bond_category,
            "isin"=>$request->isin,
            "symbol"=>$request->symbol,
            "order_action"=>$request->order_type,
            "order_status_id"=>$request->order_status,
            "user_ucc"=>$request->user_ucc,
            "order_id"=>$request->order_id,
            "tpa_code"=>$request->tpa_code,
            "basket_id"=>$request->basket_name,
            "basket_category_id"=>$request->basket_category,
            "p_isfailed"=>$request->p_isfailed,
            "flag"=>$request->flag,
            "pagenumber"=>$request->pagenumber,
            "pagesize"=>$request->pagesize,
        ]; 
    }

    public function CreateTradeReportBody($request){
        $from_date  = isset($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : null;
        $to_date    = isset($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : null;
        return [
            "user_id"=>$request->user_id,
            "order_type"=>$request->order_toggle,
            "category_id"=> $request->bond_category,
            "isin"=>$request->isin,
            "symbol"=>$request->symbol,
            "trade_action"=>$request->order_type,
            "trade_status"=>$request->trade_status,
            "user_ucc"=>$request->user_ucc,
            "trade_id"=>$request->trade_id,
            "order_id"=>$request->bsc_trade_id,
            "tpa_code"=>$request->tpa_code,
            "basket_id"=>$request->basket_id,
            "basket_category_id"=>$request->basket_category_id,
            "from_date"=>$from_date,
            "to_date"=>$to_date,
            "p_isfailed"=>$request->p_isfailed,
            "pagenumber"=>$request->pagenumber,
            "pagesize"=>$request->pagesize,
        ];
    }
    
    public function ReturnDealSlipEmailData($tradeDetails,$user_Details, $path, $flag){
        $data = [
            'id' => 'DealSlipRFQ',
            'to' => $user_Details->email_id,
            'attachmentSource' => 'S3',
            'subjectReplaceData' => $tradeDetails->init_order_dtm,
            'bodyReplaceData' => [
                'name' => $flag == 'initiator' ? $tradeDetails->init_user_name :  $tradeDetails->res_user_name,
                'date' => $flag == 'initiator' ? $tradeDetails->init_order_dtm :  $tradeDetails->res_order_dtm
            ],
            'attachment' => [
                'file1' => $path
            ]
        ]; 
        $data = json_encode($data);
        return $data;
    }
    public function ReturnDealSlipSmsData($tradeDetails, $user_Details, $flag){
        $data =  [
            'id' => 'TradeConfirmationRFQ',
            'mobileNo' => $user_Details->mobile_no,
            'replaceData' => [
                'ucc' => $flag == 'initiator' ? $tradeDetails->init_user_ucc :  $tradeDetails->res_user_ucc,
                'date' => $flag == 'initiator' ? $tradeDetails->init_order_dtm :  $tradeDetails->res_order_dtm
            ]
        ];
 
        $data = json_encode($data);
        return $data;
    }
    public function returnSpLogData($trade_no,$url, $params, $type, $mail_response,$start_time,$end_time) {
        $req = "'0', '".$trade_no."', '".($url)."', '".($params)."', '".$type."', '".$mail_response."', null, '".$start_time."', '".$end_time."'";
        return $req;
    }

    public function CreateModifyQuoteBody($body){
    return [
        "order_id" =>$body->order_id,
        "user_id" =>$body->user_id,
        "order_dtm"=>date('Y-m-d H:i:s'),
        "user_ucc_no"=>$body->user_ucc,
        "isin"=>$body->ISINNumber,
        "order_qty"=>$body->order_qty,
        "category_id"=>$body->category_id,
        "yield"=>$body->Yield,
        "clean_price"=>$body->clean_price,
        "accrued_int"=>$body->accrued_int,
        "dirty_price"=>$body->dirty_price,
        "total_consideration"=>$body->total_consideration,
        "order_status_id"=>1,
        "order_status"=>"Pending",
        "bo_id"=>$body->dp_id,
        "uploaded_by"=>$body->user_ucc,
        "created_by"=>$body->user_ucc,
        "stamp_duty"=>$body->stamp_duty,
    ];
}

public function CreateOrderReportPropUpdateBody($request){
    return [
        "order_id"=>$request->order_id,
        "user_id"=>$request->user_id,
        "dirty_price"=>$request->dirty_price,
        "order_qty"=>$request->order_qty,
        "clean_price"=>$request->clean_price,
        // "accrued_int"=>$request->accrued_int,
        "yield"=>$request->yield,
        "total_consideration"=>$request->total_consideration,
    ]; 
}


public function CreateDeleteOrderReportBody($request){
    return [
        "order_id"=>$request->order_id,
        "user_id"=>$request->user_id,
    ]; 
}

}



