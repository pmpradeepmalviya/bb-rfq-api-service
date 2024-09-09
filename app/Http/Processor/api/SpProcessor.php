<?php

namespace App\Http\Processor\api;
use Illuminate\Support\Facades\DB;
use Exception;
use Request;
use Illuminate\Support\Facades\Lang;
use App\Http\Constants\api\StatusConstants;
use Illuminate\Support\Facades\Log;

class SpProcessor {

    public function __construct(){
    }

    public function InsertAPILogs($data) {
        $req = 
        "'".$data['api_error_msg']."',
        '".$data['api_status_code']."',
        '".$data['api_request_ip']."',
        '".$data['api_request_header']."',
        '".$data['api_response_msg']."',
        '".$data['api_request_msg']."',
        '".$data['api_url']."',
        '".$data['api_internal_url_name']."',
        '".$data['trade_id']."',
        '".$data['api_request_start_dtm']."',
        '".$data['api_request_end_dtm']."'";
        try {
            DB::select("call sp_insert_api_log_rfq(".$req.")");
        } catch(Exception $e) {
            return $e->getMessage(); 
        }
    }
    public function InsertDetailsInAddRfqOrderAPITable($data) {
            $req = 
            "'".$data['order_id']."',
            '".$data['user_id']."',
            '".$data['user_ucc']."',
            '".$data['internal_order_no']."',
            '".$data['trade_id']."',
            '".$data['BondType']."',
            '".$data['DealType']."',
            '".$data['BidOfferInitiator']."',
            '".$data['ISINNumber']."',
            '".$data['Rating']."',
            '".$data['RatingAgency']."',
            '".$data['Value']."',
            '".$data['MinimumOrderValue']."',
            '".$data['TypeOfYield']."',
            '".$data['Yield']."',
            '".$data['Price']."',
            '".$data['SettlementType']."',
            '".$data['GFD']."',
            '".$data['DealTimeHours']."',
            '".$data['DealTimeMinutes']."',
            '".$data['InitiatorParticipantLoginID']."',
            '".$data['InitiatorDealerLoginID']."',
            '".$data['OTOOTM']."',
            '".$data['OTOParticipantName']."',
            '".$data['ProClient']."',
            '".$data['InitiatorName']."',
            '".$data['BuyerClientName']."',
            '".$data['SellerClientName']."',
            '".$data['DirectBrokered']."',
            '".$data['SellerBrokerName']."',
            '".$data['BuyerBrokerName']."',
            '".$data['NegotiableFlag']."',
            '".$data['DisclosedIdentity']."',
            '".$data['InitiatorCustodian']."',
            '".$data['InitiatorIFSC']."',
            '".$data['InitiatorBankAccountNumber']."',
            '".$data['InitiatorDpId']."',
            '".$data['InitiatorDpType']."',
            '".$data['InitiatorClientID']."',
            '".$data['InitiatorReferenceNumber']."',
            '".$data['InitiatorComment']."',
            '".$data['Res_Errorcode']."',
            '".$data['Res_Message']."',
            '".$data['Res_RFQOrdernumber']."',
            '".$data['Res_ISINNumber']."',
            '".$data['Res_amount']."',
            '".$data['Res_MinRespAmount']."',
            '".$data['Res_Yield']."',
            '".$data['Res_dealID']."',
            '".$data['Res_dealtime']."',
            '".$data['Res_AccuredInterest']."',
            '".$data['Res_Ordrstatus']."',
            '".$data['Res_Price']."',
            '".$data['Res_Value']."'";
       
        try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertDetailsInAddRfqOrderAPITable req'=>"sp_insert_update_add_orders_rfq(".$req.")"]);
            }
            $res=DB::select("call sp_insert_update_add_orders_rfq(".$req.")");
        } catch(Exception $e) {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertDetailsInAddRfqOrderAPITablespError'=>$e->getMessage()]);
            }
            $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }

    public function InsertDetailsInAcceptQuoteAPITable($data) {
        $req = 
            "'".$data['order_id']."',
            '".$data['user_id']."',
            '".$data['user_ucc']."',
            '".$data['internal_order_no']."',
            '".$data['trade_id']."',
            '".$data['BondType']."',
            '".$data['DealType']."',
            '".$data['BidOfferResponder']."',
            '".$data['ISINNumber']."',
            '".$data['RFQaddOrderNumber']."',
            '".$data['RFQaddOrderdealid']."',
            '".$data['ResponderParticipantLoginID']."',
            '".$data['ResponderDealerLoginID']."',
            '".$data['ModAcrInt']."',
            '".$data['TotalConsideration']."',
            '".$data['ProClient']."',
            '".$data['BuyerClientName']."',
            '".$data['SellerClientName']."',
            '".$data['DirectBrokered']."',
            '".$data['SellerBrokerName']."',
            '".$data['BuyerBrokerName']."',
            '".$data['ResponderName']."',
            '".$data['ResponderCustodian']."',
            '".$data['ResponderBankIFSC']."',
            '".$data['ResponderAccountNumber']."',
            '".$data['ResponderDpType']."',
            '".$data['ResponderDpId']."',
            '".$data['ResponderDpClientID']."',
            '".$data['ResponderReferanceNumber']."',
            '".$data['ResponderComment']."',
            '".$data['Res_Errorcode']."',
            '".$data['Res_Message']."',
            '".$data['Res_RFQOrdernumber']."',
            '".$data['Res_ISINNumber']."',
            '".$data['Res_amount']."',
            '".$data['Res_Yield']."',
            '".$data['Res_Price']."',
            '".$data['Res_AccuredInterest']."',
            '".$data['Res_Ordrstatus']."',
            '".$data['Res_dealtime']."',
            '".$data['Res_amount']."',
            '".$data['Res_RFQOrdernumber']."',   
            '".$data['Res_dealID']."'";
       
        try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertDetailsInAcceptQuoteAPITable req'=>"sp_insert_update_accept_quote_rfq(".$req.")"]);
            }
            $res=DB::select("call sp_insert_update_accept_quote_rfq(".$req.")");
        } catch(Exception $e) {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertDetailsInAcceptQuoteAPITableError'=>$e->getMessage()]);
            }
            $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }

    public function InsertResponderOrdbookEntryBody($data)
     {
       $req=
       "'".$data['order_id']."',
        '".$data['user_id']."',
        '".$data['order_dtm']."',
        '".$data['user_ucc_no']."',
        '".$data['order_type']."',
        '".$data['basket_category_id']."',
        '".$data['basket_category_name']."',
        '".$data['basket_id']."',
        '".$data['basket_name']."',
        '".$data['basket_display_name']."',
        '".$data['basket_qty']."',
        '".$data['tpa_id']."',
        '".$data['tpa_name']."',
        '".$data['isin']."',
        '".$data['issuer_name']."',
        '".$data['order_action']."',
        '".$data['order_qty']."',
        '".$data['executed_qty']."',
        '".$data['pending_qty']."',
        '".$data['category_id']."',
        '".$data['yield']."',
        '".$data['clean_price']."',
        '".$data['accrued_int']."',
        '".$data['dirty_price']."',
        '".$data['total_consideration']."',
        '".$data['order_status_id']."',
        '".$data['order_status']."',
        '".$data['parent_order_no']."',
        '".$data['weighted_avg_yield']."',
        '".$data['total_basket_price']."',
        '".$data['bo_id']."',
        '".$data['bank_acc_no']."',
        '".$data['user_id']."',
        '".$data['user_id']."',
        '".$data['brokerage_percentage']."',
        '".$data['brokerage_amount']."',
        '".$data['stamp_duty']."',
        '".$data['settlement_amount']."',
        '".$data['SettlementType']."',
        '".$data['c2t_user_id']."',
        '".$data['tpa_unique_id']."'";        
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['InsertResponderOrdbookEntryBody req'=>"sp_place_order_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_place_order_rfq(".$req.")");
        } catch(Exception $e) {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['InsertResponderOrdbookEntryBodyError'=>$e->getMessage()]);
        }
        $res=["error"=>$e->getMessage()]; 
        }
        return $res;
    }
    
    public function updateTradebook($data,$type)
    {
        //dd($data,"bgh");
        if($type=="addorder"){
            $req=
            "'".$data['trade_id']."',
            '".$data['user_ucc']."',
            '".$data['AcceptOrderDealID']."',
            '".$data['AcceptOrderNo']."',
            NULL,
            NULL,
            NULL,
            NULL,
            '".$data['user_id']."'";

        }else{
            $req=
            "'".$data['trade_id']."',
            '".$data['user_ucc']."',
            NULL,
            NULL,
            '".$data['quoteDealid']."',
            '".$data['quoteRFQOrderno']."',
            '".$data['quoteFinalOrderNo']."',
            NULL,
            '".$data['user_id']."'";
        }
      
       try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['updateTradebook req'=>"sp_update_tradebook_rfq(".$req.")",'type'=>$type]);
        }
       $res=DB::select("call sp_update_tradebook_rfq(".$req.")");
       } catch(Exception $e) {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['updateTradebookError'=>$e->getMessage()]);
        }
       $res=["error"=>$e->getMessage()]; 
       }
       return $res;
    }

    public function InsertNewInitiatorQuoteBody($data)
    {
        $req=
        "'".$data['order_id']."',
        '".$data['user_id']."',
            '".$data['order_dtm']."',
            '".$data['user_ucc_no']."',
            '".$data['order_type']."',
            '".$data['tpa_id']."',
            '".$data['tpa_code']."',
            '".$data['tpa_name']."',
            '".$data['isin']."',
            '".$data['issuer_name']."',
            '".$data['order_action']."',
            '".$data['order_qty']."',
            '".$data['category_id']."',
            '".$data['yield']."',
            '".$data['clean_price']."',
            '".$data['accrued_int']."',
            '".$data['dirty_price']."',
            '".$data['total_consideration']."',
            '".$data['order_status_id']."',
            '".$data['order_status']."',
            '".$data['bo_id']."',
            '".$data['bank_acc_no']."',
            '".$data['settlement_type']."',
            '".$data['c2t_user_id']."',
            '".$data['user_id']."',
            '".$data['user_id']."'";
            
            try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertNewInitiatorQuoteBody req'=>"sp_add_new_quote_initiatior_rfq(".$req.")"]);
            }
            $res=DB::select("call sp_add_new_quote_initiatior_rfq(".$req.")");
            } catch(Exception $e) {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertNewInitiatorQuoteBodyError'=>$e->getMessage()]);
            }
            $res=["error"=>$e->getMessage()]; 
            }
        return $res;
    }

    public function getTradeDetails($tradeno) {
        $req="'".$tradeno."',1,1";
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_get_tradebook_details_rfq_req'=>"call sp_get_tradebook_details_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_get_tradebook_details_rfq(".$req.")");
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_get_tradebook_details_rfq_response'=>$res]);
        }
        } catch(Exception $e) {
        Log::channel("query")->info(['sp_get_tradebook_details_rfq_error'=>$e->getMessage()]);
        $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }

    public function getDealslipDetails($tradeno) {
        $req="'".$tradeno."'";
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_get_deal_slip_rfq_req'=>"call sp_get_deal_slip_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_get_deal_slip_rfq(".$req.")");
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_get_deal_slip_rfq_response'=>$res]);
        }
        } catch(Exception $e) {
        Log::channel("query")->info(['sp_get_deal_slip_rfq_error'=>$e->getMessage()]);
        $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }

    public function UpdateDealbookFlagsShilpiTable($tradeno,$orderno,$flag) {
        
        $req="'".$tradeno."','".$orderno."','".$flag."'";
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_update_dealbook_shilpi_reported_rfq_req'=>"call sp_update_dealbook_shilpi_reported_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_update_dealbook_shilpi_reported_rfq(".$req.")");
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_update_dealbook_shilpi_reported_rfq_response'=>$res]);
        }
        } catch(Exception $e) {
        Log::channel("query")->info(['sp_update_dealbook_shilpi_reported_rfq_error'=>$e->getMessage()]);
        $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }

    public function UpdateDilslipFilePath($tradeno,$orderno,$path) {
        
        $req="'".$tradeno."','".$orderno."','".$path."'";
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_update_deal_slip_rfq_req'=>"call sp_update_deal_slip_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_update_deal_slip_rfq(".$req.")");
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_update_deal_slip_rfq_response'=>$res]);
        }
        } catch(Exception $e) {
        Log::channel("query")->info(['sp_update_deal_slip_rfq_error'=>$e->getMessage()]);
        $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }
    
    public function updateFlagsInTradebook($data)
    {
        $req=
        "'".$data['user_id']."',
        '".$data['trade_id']."',
        '".$data['initiator_email']."',
        '".$data['initiator_sms']."',
        '".$data['responder_email']."',
        '".$data['responder_sms']."'";
       try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['updateFlagsInTradebook req'=>"sp_save_notification_flags_tradebook_rfq(".$req.")"]);
        }
       $res=DB::select("call sp_save_notification_flags_tradebook_rfq(".$req.")");
       } catch(Exception $e) {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['updateFlagsInTradebookError'=>$e->getMessage()]);
        }
       $res=["error"=>$e->getMessage()]; 
       }
       return $res;
    }
    
    public function RevertQtyAndStatus($data)
    {
       $req=
       "'".$data['user_id']."',
        '".$data['order_id']."',
        '".$data['order_status_id']."',
        '".$data['order_status']."',
        '".$data['order_qty']."',
        '".$data['parent_order_id']."',
        '".$data['trade_id']."',
        '".$data['trade_status']."'";
        
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['RevertQtyAndStatus req'=>"sp_update_failed_order_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_update_failed_order_rfq(".$req.")");
        } catch(Exception $e) {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['RevertQtyAndStatusError'=>$e->getMessage()]);
        }
        $res=["error"=>$e->getMessage()]; 
        }
        return $res;
    }  

    public function InsertIntoTxnTable($data)
    {
        
        $req=
        "NULL,
        '".$data['order_no']."',
        '".$data['trade_no']."',
        '".$data['payment_link']."',
        '".$data['link_status']."',
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '".$data['user_id']."'";
        try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertIntoTxnTable req'=>"sp_insert_payment_link_rfq(".$req.")"]);
            }
            $res=DB::select("call sp_insert_payment_link_rfq(".$req.")");
        } catch(Exception $e) {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertIntoTxnTableError'=>$e->getMessage()]);
            }
            $res=["error"=>$e->getMessage()]; 
        }        
        return $res;   
    }

     //==== For login sp call ========
     public function validateUserSp($username, $password, $tpa_code){
        try {
            $req="'".$username."','".$password."','".$tpa_code."'";
            $res=DB::select("call sp_admin_validate_user_login(".$req.")");
            return $res;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    ////////////////////////////Admin functions///////////////////////
    public function getOrderReportData($data){
    
    $req = 
        "'".$data['user_id']."',
        '".$data['order_type']."',
        '".$data['category_id']."',
        '".$data['isin']."',
        '".$data['symbol']."',
        '".$data['order_action']."',
        '".$data['order_status_id']."',
        '".$data['user_ucc']."',
        '".$data['order_id']."',
        '".$data['tpa_code']."',
        '".$data['basket_id']."',
        '".$data['basket_category_id']."',
        '".$data['p_isfailed']."',
        '".$data['flag']."',
        '".$data['pagenumber']."',
        '".$data['pagesize']."'";
       
        
       
        try {
            $res = DB::select('call sp_admin_get_orders_report_rfq(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)', [
                $data['user_id'],$data['order_type'],$data['category_id'],$data['isin'],$data['symbol'],$data['order_action'],
                $data['order_status_id'],$data['user_ucc'],$data['order_id'],$data['tpa_code'],$data['basket_id'],
                $data['basket_category_id'],$data['p_isfailed'] ? 1 : 0,$data['flag'],$data['pagenumber'],$data['pagesize']
            ]);
        
            \Log::info("Call SP get order report details: " . json_encode($res));
            $response = json_decode(json_encode(['data' => $res]), true);
            return $response;
        } catch(Exception $e) {
            return $e->getMessage(); 
        }
    }

   public function getTradeReportData($data){
    
    $req = 
        "'".$data['user_id']."',
        '".$data['order_type']."',
        '".$data['category_id']."',
        '".$data['isin']."',
        '".$data['symbol']."',
        '".$data['trade_action']."',
        '".$data['trade_status']."',
        '".$data['user_ucc']."',
        '".$data['trade_id']."',
        '".$data['order_id']."',
        '".$data['tpa_code']."',
        '".$data['basket_id']."',
        '".$data['basket_category_id']."',
        '".$data['from_date']."',
        '".$data['to_date']."',
        '".$data['p_isfailed']."',
        '".$data['pagenumber']."',
        '".$data['pagesize']."'";
     
        try {
            $res = DB::select('call sp_admin_get_trade_history_report_rfq(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                $data['user_id'],$data['order_type'],$data['category_id'],$data['isin'],$data['symbol'],$data['trade_action'],
                $data['trade_status'],$data['user_ucc'],$data['trade_id'],$data['order_id'],$data['tpa_code'],$data['basket_id'],
                $data['basket_category_id'],$data['from_date'],$data['to_date'],$data['p_isfailed']? 1 : 0,$data['pagenumber'],$data['pagesize']
            ]);
            
            \Log::info("Call SP get trade history report details: " . json_encode($res));
            $response = json_decode(json_encode(['data' => $res]), true);
            return $response;
        } catch(Exception $e) {
            return $e->getMessage(); 
        } 
    }
    public function updateFlagsInTradebookAdmin($data)
    {
       try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info('updateFlagsInTradebookAdmin req: ' . json_encode(['sp_save_notification_flags_tradebook_rfq' => $data]));
            }
            $res = DB::select("call sp_save_notification_flags_tradebook_rfq(?, ?, ?, ?, ?, ?)", [
                $data['user_id'] ? $data['user_id'] : null,
                $data['trade_id'] ? $data['trade_id'] : null,
                $data['initiator_email'] ? $data['initiator_email'] : null ,
                $data['initiator_sms'] ? $data['initiator_sms'] : null,
                $data['responder_email'] ? $data['responder_email'] : null,
                $data['responder_sms'] ? $data['responder_sms'] : null

            ]);

            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info('updateFlagsInTradebookAdmin res: ' . json_encode($res));
            }
        } catch(Exception $e) {
            if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['updateFlagsInTradebookError'=>$e->getMessage()]);
        }
       $res=["error"=>$e->getMessage()]; 
       }
       return $res;
    }
    
    public function notificationLogs($sp_log_param){
        try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['updateFlagsInTradebook req'=>"sp_save_notification_flags_tradebook_rfq(".$sp_log_param.")"]);
            }
            $res=DB::select("call sp_insert_update_rfq_email_notification(".$sp_log_param.")");   
            if(config('constant.FILE_LOG_REQUIRED')==true){ 
                Log::channel("query")->info(['updateFlagsInTradebook res'=> $res]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function getDealBookDetails($tradeno,$orderno) {
        $req="'".$tradeno."','".$orderno."'";
        try {
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_get_deal_book_rfq_req'=>"call sp_get_deal_book_rfq(".$req.")"]);
        }
        $res=DB::select("call sp_get_deal_book_rfq(".$req.")");
        if(config('constant.FILE_LOG_REQUIRED')==true){
            Log::channel("query")->info(['sp_get_deal_book_rfq_response'=>$res]);
        }
        } catch(Exception $e) {
        Log::channel("query")->info(['sp_get_deal_book_rfq_error'=>$e->getMessage()]);
        $res=["error"=>$e->getMessage()];  
        }
        return $res;
    }

    public function InsertModifyQuoteBody($data)
    {
        $req=
        "'".$data['order_id']."',
        '".$data['dirty_price']."',
        '".$data['order_qty']."',
        '".$data['clean_price']."',
        '".$data['accrued_int']."',
        '".$data['yield']."',
        '".$data['user_id']."'";
            
        try {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertModifyQuoteBody req'=>"sp_update_order_rfq(".$req.")"]);
            }
            $res=DB::select("call sp_update_order_rfq(".$req.")");
            } catch(Exception $e) {
            if(config('constant.FILE_LOG_REQUIRED')==true){
                Log::channel("query")->info(['InsertModifyQuoteBodyError'=>$e->getMessage()]);
            }
            $res=["error"=>$e->getMessage()]; 
        }
        return $res;
    }

    //==================== For Order Report Prop Update Sp Call ==================================
    public function orderReportPropUpdate($data){
        $req = 
            "'".$data['order_id']."',
            '".$data['user_id']."',
            '".$data['dirty_price']."',
            '".$data['order_qty']."',
            '".$data['clean_price']."',
            '".$data['yield']."',
            '".$data['total_consideration']."'";       
            
        try {
            $res = DB::select('call sp_admin_update_order_report_rfq(?, ?, ?, ?, ?, ?, ?,?)', [
                $data['order_id'],$data['order_qty'],$data['dirty_price'],$data['clean_price'],$data['yield'],
                $data['total_consideration'],'Yes' ,$data['user_id']
            ]);
        
            \Log::info("Call SP update order report prop: " . json_encode($res));
            $response = json_decode(json_encode(['data' => $res]), true);
            return $response;
        } catch(Exception $e) {
            return $e->getMessage(); 
        }
    }

    //==================== For Delete Order Report Sp Call ==================================
    public function deleteOrderReport($data){
        $req = 
            "'".$data['order_id']."',
            '".$data['user_id']."'";       
            
        try {
            $res = DB::select('call sp_admin_delete_order_report_rfq(?, ?, ?)', [
                $data['order_id'],$data['user_id'],'Yes'
            ]);
        
            \Log::info("Call SP delete order report: " . json_encode($res));
            $response = json_decode(json_encode(['data' => $res]), true);
            return $response;
        } catch(Exception $e) {
            return $e->getMessage(); 
        }
    }

} 