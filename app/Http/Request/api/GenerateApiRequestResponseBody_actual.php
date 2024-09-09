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
                    "InitiatorParticipantLoginID" => "BONDBSPL",
                    "InitiatorDealerLoginID" => "BBSPL01",
                    "OTOOTM" =>"OTO",
                    "OTOParticipantName" =>"BONDBSPL",// as per akshya's email: Offer - Bid API Testing Successful
                    "ProClient" => "CLIENT",
                    "InitiatorName" => "BONDBSPL",
                    "BuyerClientName" => $request->BuyerClientName,//initiator_ucc
                    "SellerClientName" => $request->SellerClientName,//responder_ucc
                    "DirectBrokered" => "BROKERED",
                    "SellerBrokerName" => "BONDBSPL",
                    "BuyerBrokerName" => "BONDBSPL",
                    "NegotiableFlag" => "NO",
                    "DisclosedIdentity" => "NO",
                    "InitiatorCustodian" => "",
                    "InitiatorIFSC" => $request->InitiatorIFSC,
                    "InitiatorBankAccountNumber" => $request->InitiatorBankAccountNumber,
                    "InitiatorDpType" => "CDSL",
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
                    "ResponderName"=>"BONDBSPL",//as per excel $request->user_ucc,
                    "ResponderCustodian"=>"",
                    "ResponderBankIFSC"=>$request->ResponderBankIFSC,
                    "ResponderAccountNumber"=>$request->ResponderAccountNumber,
                    "ResponderDpType"=>"CDSL",
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

    // LOGIN API DATA
    public function CreateShilpiReqBody($request,$type){
        // INITIALIZE A REQUEST
        $shilpiurl = config("constant.PROD_SHLIPIURL");
        $decodedData=(json_decode(json_encode($request),true));
        $date=explode(" ",$decodedData['trade_dtm']);
        $total_consideration_without_stampduty=$decodedData['traded_value']-$decodedData['stamp_duty'];
        $total_consideration_with_stampduty=$decodedData['traded_value']+$decodedData['stamp_duty'];
        if($type=="initiator"){
            
            $xmlbody=$shilpiurl."<?xml version=1.0 encoding=utf-8?>
            <transactiondetails>
                <dealbook>
                <orderdate>".$date[0]."</orderdate>
                <ordertime>".$date[1]."</ordertime>
                <orderno>".$decodedData['quote_ordernumber']."</orderno>
                <tradedate>".$date[0]."</tradedate>
                <tradetime>".$date[1]."</tradetime>
                <tradeno>".$decodedData['trade_id']."</tradeno>
                <dealid>".$decodedData['trade_id']."</dealid>
                <ucccode>".$decodedData['init_user_ucc']."</ucccode>
                <participantname>".$decodedData['init_user_name']."</participantname>
                <isin>".$decodedData['isin']."</isin>
                <securityname>".$decodedData['issuer_name']."</securityname>
                <dealtype>".$decodedData['init_order_action']."</dealtype>
                <quantity>".$decodedData['init_order_qty']."</quantity>
                <facevalue>".$decodedData['dirty_price']."</facevalue>
                <couponrate>".$decodedData['init_order_action']."</couponrate>
                <maturitydate>2024-11-23</maturitydate>
                <cleanprice>".$decodedData['clean_price']."</cleanprice>
                <accruedinterest>".$decodedData['accrued_int']."</accruedinterest>
                <modifiedaccruedinterest>".$decodedData['accrued_int']."</modifiedaccruedinterest>
                <dirtyprice>".$decodedData['dirty_price']."</dirtyprice>
                <totalconsiderationbefore>$total_consideration_without_stampduty</totalconsiderationbefore>
                <rateofstampduty></rateofstampduty>
                <stampdutyseller>".$decodedData['stamp_duty']."</stampdutyseller>
                <totalconsiderationafter>$total_consideration_with_stampduty</totalconsiderationafter>
                <yieldtype>YTM</yieldtype>
                <yield>".$decodedData['yield']."</yield>
                <dealtby></dealtby>
                <nameofbroker>BBSPL</nameofbroker>
                <settlementschedule>T 1</settlementschedule>
                <settlementdate>2024-01-22</settlementdate>
                <settlementno>2324192</settlementno>
                <dealerid></dealerid>
                <dealername></dealername>
                <bankname>HDFC BANK LTD</bankname>
                <bankaccountno>57500001086245</bankaccountno
                <ifsccode>HDFC0000060</ifsccode>
                <dpname>CDSL</dpname>
                <dptype></dptype>
                <dpid></dpid>
                <benefeciaryid></benefeciaryid>
                <intenttosettle>Yes</intenttosettle>
                <reportingon>NSDRST</reportingon>
                <settlement>Pending</settlement>
                <producttype>CB</producttype>
                <ndrstordernumber>202401150703541</ndrstordernumber>
                <couponfrequency>Semi Annually</couponfrequency>
                <pricipalamount>1082</pricipalamount>
                <redemptiondate></redemptiondate>
                <redemptionamount></redemptionamount>
                <calldate></calldate>
                <putdate></putdate>
                <settlementdate></settlementdate>
                </dealbook>
            </transactiondetails>";
        }else{
            $xmlbody=$shilpiurl."<?xml version=1.0 encoding=utf-8?>
            <transactiondetails>
                <dealbook>
                <orderdate>".$date[0]."</orderdate>
                <ordertime>".$date[1]."</ordertime>
                <orderno>".$decodedData['quote_ordernumber']."</orderno>
                <tradedate>".$date[0]."</tradedate>
                <tradetime>".$date[1]."</tradetime>
                <tradeno>".$decodedData['trade_id']."</tradeno>
                <dealid>".$decodedData['trade_id']."</dealid>
                <ucccode>".$decodedData['res_user_ucc']."</ucccode>
                <participantname>".$decodedData['res_user_name']."</participantname>
                <isin>".$decodedData['isin']."</isin>
                <securityname>".$decodedData['issuer_name']."</securityname>
                <dealtype>".$decodedData['res_order_action']."</dealtype>
                <quantity>".$decodedData['res_order_qty']."</quantity>
                <facevalue>".$decodedData['dirty_price']."</facevalue>
                <couponrate>".$decodedData['res_order_action']."</couponrate>
                <maturitydate>2024-11-23</maturitydate>
                <cleanprice>".$decodedData['clean_price']."</cleanprice>
                <accruedinterest>".$decodedData['accrued_int']."</accruedinterest>
                <modifiedaccruedinterest>".$decodedData['accrued_int']."</modifiedaccruedinterest>
                <dirtyprice>".$decodedData['dirty_price']."</dirtyprice>
                <totalconsiderationbefore>$total_consideration_without_stampduty</totalconsiderationbefore>
                <rateofstampduty></rateofstampduty>
                <stampdutyseller>".$decodedData['stamp_duty']."</stampdutyseller>
                <totalconsiderationafter>$total_consideration_with_stampduty</totalconsiderationafter>
                <yieldtype>YTM</yieldtype>
                <yield>".$decodedData['yield']."</yield>
                <dealtby></dealtby>
                <nameofbroker>BBSPL</nameofbroker>
                <settlementschedule>T 1</settlementschedule>
                <settlementdate>2024-01-22</settlementdate>
                <settlementno>2324192</settlementno>
                <dealerid></dealerid>
                <dealername></dealername>
                <bankname>HDFC BANK LTD</bankname>
                <bankaccountno>57500001086245</bankaccountno>
                <ifsccode>HDFC0000060</ifsccode>
                <dpname>CDSL</dpname>
                <dptype></dptype>
                <dpid></dpid>
                <benefeciaryid></benefeciaryid>
                <intenttosettle>Yes</intenttosettle>
                <reportingon>NSDRST</reportingon>
                <settlement>Pending</settlement>
                <producttype>CB</producttype>
                <ndrstordernumber>202401150703541</ndrstordernumber>
                <couponfrequency>Semi Annually</couponfrequency>
                <pricipalamount>1082</pricipalamount>
                <redemptiondate></redemptiondate>
                <redemptionamount></redemptionamount>
                <calldate></calldate>
                <putdate></putdate>
                <settlementdate></settlementdate>
                </dealbook>
            </transactiondetails>";
        }
        $data = [
            'api_name'=> 'ShilpiReporting',
            'request_name' => 'ShilpiReporting',
            'method_name' => 'GET',
            'headers' =>[],
            'url' => $xmlbody,
        ];
        return $data;
    }

    public function CreateDealSlipBody($tradeDetails,$type){
        if ($type=="initiator"){
            return [ 
                "name"=>$tradeDetails[0]->init_user_name,
                "ucc"=>$tradeDetails[0]->init_user_ucc,
                "order_type"=>$tradeDetails[0]->init_order_action,
                "order_no"=>$tradeDetails[0]->accept_order_rfq_deal_id,// told by akshya while discussing shilpi params,
                "trade_no"=>$tradeDetails[0]->trade_id,
                "order_dtm"=>$tradeDetails[0]->init_order_dtm,
                "trade_dtm"=>$tradeDetails[0]->trade_dtm,
                "isin"=>$tradeDetails[0]->isin,
                "face_value"=>$tradeDetails[0]->dirty_price,//need to confirm  mapping
                "yield"=>$tradeDetails[0]->yield,
                "coupon_rate"=>$tradeDetails[0]->coupon_rate,//asked db team to add column once done will chnge
                "maturity_date"=>$tradeDetails[0]->maturity_date,//asked db team to add column once done will chnge
                "security_name"=>$tradeDetails[0]->issuer_name,
                "clean_price"=>$tradeDetails[0]->clean_price,
                "accrued_interest"=>$tradeDetails[0]->accrued_int,
                "dirty_price"=>$tradeDetails[0]->dirty_price,
                "stamp_duty"=>strtolower($tradeDetails[0]->init_order_action)=='sell'?0.00:$tradeDetails[0]->stamp_duty,//told by akshya while discussing shilpi params
                "quantity"=>$tradeDetails[0]->init_order_qty,
                "total_consideration"=>$tradeDetails[0]->traded_value,
                "settlement_no"=>$tradeDetails[0]->settlement_no,
                "settlement_date"=>$tradeDetails[0]->settlement_date,
                "settlement_type"=>$tradeDetails[0]->settlement_type,
                "settlement_amount"=>$tradeDetails[0]->settlement_amount,
                "dpid"=>$tradeDetails[0]->init_dp_id,
            ];
           
        }else{
           return [ 
                "name"=>$tradeDetails[0]->res_user_name,
                "ucc"=>$tradeDetails[0]->res_user_ucc,
                "order_type"=>$tradeDetails[0]->res_order_action,
                "order_no"=>$tradeDetails[0]->quote_ordernumber,//told by aksaya while discussing about shilpi params,
                "trade_no"=>$tradeDetails[0]->trade_id,
                "order_dtm"=>$tradeDetails[0]->res_order_dtm,
                "trade_dtm"=>$tradeDetails[0]->trade_dtm,
                "isin"=>$tradeDetails[0]->isin,
                "face_value"=>$tradeDetails[0]->dirty_price,//need to confirm  mapping
                "yield"=>$tradeDetails[0]->yield,
                "coupon_rate"=>$tradeDetails[0]->coupon_rate,//asked db team to add column once done will chnge
                "maturity_date"=>$tradeDetails[0]->maturity_date,//asked db team to add column once done will chnge
                "security_name"=>$tradeDetails[0]->issuer_name,
                "clean_price"=>$tradeDetails[0]->clean_price,
                "accrued_interest"=>$tradeDetails[0]->accrued_int,
                "dirty_price"=>$tradeDetails[0]->dirty_price,
                "stamp_duty"=>strtolower($tradeDetails[0]->res_order_action)=='sell'?0.00:$tradeDetails[0]->stamp_duty,//told by akshya while discussing shilpi params,
                "quantity"=>$tradeDetails[0]->res_order_qty,
                "total_consideration"=>$tradeDetails[0]->traded_value,
                "settlement_no"=>$tradeDetails[0]->settlement_no,
                "settlement_date"=>$tradeDetails[0]->settlement_date,
                "settlement_type"=>$tradeDetails[0]->settlement_type,
                "settlement_amount"=>$tradeDetails[0]->settlement_amount,
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
            "pagenumber"=>$request->pagenumber,
            "pagesize"=>$request->pagesize,
        ]; 
    }

    public function CreateTradeReportBody($request){
       
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
            "from_date"=>date('Y-m-d',strtotime($request->from_date)),
            "to_date"=>date('Y-m-d',strtotime($request->to_date)),
            "p_isfailed"=>$request->p_isfailed,
            "pagenumber"=>$request->pagenumber,
            "pagesize"=>$request->pagesize,
        ];
    }
    
    public function ReturnDealSlipEmailData($tradeDetails,$user_Details, $path, $flag){
        $data = [
            'id' => 'DealSlip',
            'to' => $user_Details->email_id,
            'attachmentSource' => 'S3',
            'subjectReplaceData' => date("d/m/Y", strtotime($tradeDetails->init_order_dtm)),
            'bodyReplaceData' => [
                'name' => $flag == 'initiator' ? $tradeDetails->init_user_name :  $tradeDetails->res_user_name,
                'date' => date("d/m/Y", strtotime($flag == 'initiator' ? $tradeDetails->init_order_dtm :  $tradeDetails->res_order_dtm)),
                'ucc' => $flag == 'initiator' ? $tradeDetails->init_user_ucc : $tradeDetails->res_user_ucc
            ],
            'attachment' => [
                'file1' => $path
            ]
        ]; 
        $data = json_encode($data);
        return $data;
    }
    public function ReturnDealSlipSmsData($user_Details){
        $data =  [
            'id' => 'DealSlip',
            'mobileNo' => $user_Details->mobile_no,
            'replaceData' => [
                'path' => 'uat.bondbazaar.com'
            ]
        ];

        $data = json_encode($data);

        return $data;
    }
    public function returnSpLogData($trade_no,$url, $params, $type, $mail_response,$start_time,$end_time) {
        $req = "'0', '".$trade_no."', '".($url)."', '".($params)."', '".$type."', '".$mail_response."', null, '".$start_time."', '".$end_time."'";
        return $req;
    }
}



