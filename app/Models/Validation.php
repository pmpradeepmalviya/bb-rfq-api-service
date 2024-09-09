<?php

namespace App\Models;
use Illuminate\Support\Facades\Lang;
use App\Http\Services\api\ApiService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class Validation
{
    public $Service;

    public function __construct(){
        $this->Service = new ApiService();
    }

    // VALIDATE IPO MASTER API REQUEST DATA
    public function ValidateIpoRequest($request){
        $validator = Validator::make($request->all(), [
            'exchange' => 'required',
            'category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    // VALIDATE CHANGE PASSWORD
    public function ValidateChangePassword($request){
        $validator = Validator::make($request->all(), [
            'oldpassword' => 'required',
            'newpassword' => 'required',
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    // VALIDATE IPO PLACE ORDER
    public function ValidateIPOPlaceOrder($request){
        $validator = Validator::make($request->all(), [
            'ipo_id'=>'required',
            'symbol'=>'required',
            'applicationNumber'=>'required',
            'category'=>'required',
            'depository'=>'required',
            'dpId'=>'required',
            'clientBenId' => 'required',
            'pan' => 'required',
            'referenceNumber' => 'required',
            'allotmentMode' => 'required',
            'upiFlag' => 'required',
            'upi' => 'required',
            'bids.*.activityType' => 'required',
            'bids.*.series' => 'required',
            'bids.*.quantity' => 'required',
            'bids.*.price' => 'required',
            'bids.*.amount' => 'required'
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    // VALIDATE HEADER TOKEN
    public function ValidateHeaderToken($request,$AuthKeyType){
       
        // check whether the access-token present in the header or not
        $HeaderToken = $request->header('Authorization');
        if($HeaderToken == null || $HeaderToken == ""){
            return [
                'error_code' => config('error.1004')
            ];
        }
        // header-token format validation
        $TokenArr = explode(" ",$HeaderToken);
        if(count($TokenArr) != 2){
            return [
                'error_code' => config('error.1004')
            ];
        }
        //dd(config("constant.TOKEN_RFQ_API_SERVICE"),"kkk");
        if($TokenArr[1]!=config("constant.TOKEN_RFQ_API_SERVICE")){
            return [
                'error_code' => config('error.1004')
            ];
        }
        //send response
        return [
            'error_code' => null,
            'token' => $TokenArr[0]
        ];
    }

    public function ValidateAddRFQOrder($request){
        $validator = Validator::make($request->all(), [
            "user_id"=>"required",
            "user_ucc"=>"required",
            "basket_category_id"=>"required",
            "basket_category_name"=>"required",
            "basket_qty"=>"required",
            "order_qty"=>"required",
            "executed_qty"=>"required",
            "pending_qty"=>"required",
            "category_id"=>"required",
            "clean_price"=>"required",
            "accrued_int"=>"required",
            "dirty_price"=>"required",
            "total_consideration"=>"required",
            "parent_order_no"=>"required",
            "weighted_avg_yield"=>"required",
            "total_basket_price"=>"required",
            "internal_order_no"=>"required",// this is order no of parent 
			"BidOfferInitiator"=> "required",
            "BidOfferResponder"=> "required",
			"ISINNumber"=> "required",
			"Rating"=> "nullable",
			"RatingAgency"=> "nullable",
			"Value"=> "required|numeric",
			"Yield"=> "required|numeric",
			"Price"=> "required|numeric",
			"OTOParticipantName"=> "required",
			"SellerClientName"=> "required",
			"InitiatorIFSC"=> "required",
			"InitiatorBankAccountNumber"=> "required",
			"InitiatorDpId"=> "required",
			"InitiatorClientID"=> "required",
			"InternalOrderNumber"=> "required",
		]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    public function ValidateAddRfqQuote($request){
        $validator = Validator::make($request->all(), [
            "user_id"=>"required",
            "user_ucc"=>"required",
            "basket_category_id"=>"required",
            "basket_category_name"=>"required",
            "basket_qty"=>"required",
            "order_qty"=>"required",
            "executed_qty"=>"required",
            "pending_qty"=>"required",
            "category_id"=>"required",
            "clean_price"=>"required",
            "accrued_int"=>"required",
            "dirty_price"=>"required",
            "total_consideration"=>"required",
            "parent_order_no"=>"required",
            "weighted_avg_yield"=>"required",
            "total_basket_price"=>"required",
            "internal_order_no"=>"required",// this is order no of parent 
			"BidOfferInitiator"=> "required",
            "BidOfferResponder"=> "required",
			"ISINNumber"=> "required",
			"Rating"=> "nullable",
			"RatingAgency"=> "nullable",
			"Value"=> "required|numeric",
			"Yield"=> "required|numeric",
			"Price"=> "required|numeric",
			"OTOParticipantName"=> "required",
			"SellerClientName"=> "required",
			"ResponderBankIFSC"=> "required",
            "ResponderAccountNumber"=> "required",
            "ResponderDpId"=> "required",
            "ResponderDpClientID"=> "required",
			"InternalOrderNumber"=> "required",
		]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    public function ValidateProcessOrder($request){
        $validator = Validator::make($request->all(), [
            "user_id"=>"required",
            "user_ucc"=>"required",
            "tpa_id"=>"required",
            "order_qty"=>"required",
            "executed_qty"=>"required",
            "pending_qty"=>"required",
            "category_id"=>"required",
            "clean_price"=>"required",
            "accrued_int"=>"required",
            "dirty_price"=>"required",
             "total_consideration_popup"=>"required",
            "BidOfferInitiator"=> "required",
            "BidOfferResponder"=> "required",
            "ISINNumber"=> "required",
            "Rating"=> "nullable",
            "RatingAgency"=> "nullable",
            "Value"=> "required|numeric",
            "Yield"=> "required|numeric",
            "Price"=> "required|numeric",
            "OTOParticipantName"=> "required",
            "SellerClientName"=> "required",
            "ResponderBankIFSC"=> "required",
            "ResponderAccountNumber"=> "required",
            "ResponderDpId"=> "required",
            "ResponderDpClientID"=> "required",
            "InitiatorIFSC"=> "required",
            "InitiatorBankAccountNumber"=> "required",
            "InitiatorDpId"=> "required",
            "InitiatorClientID"=> "required",
            "InternalOrderNumber"=> "required",
            "brokerage_percentage"=> "required",
            "brokerage_amount"=> "required",
            "stamp_duty"=> "required",
            "settlement_amount"=>"required",
            "client_ip"=>"required",
            "BondCategory"=>"required"
		]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }
    public function ValidateNewInitiatorQuote($request){
        $validator = Validator::make($request->all(), [
            "user_id"=>"required",
            "user_ucc"=>"required",
            "tpa_id"=>"required",
            "order_qty"=>"required",
            "category_id"=>"required",// need clarity
            "clean_price"=>"required",
            "accrued_int"=>"required",
            "dirty_price"=>"required",
            "total_consideration"=>"required",
            "QuoteType"=> "required",//sell/buy
            "ISINNumber"=> "required",
            "Rating"=> "nullable",
            "RatingAgency"=> "nullable",
            "Yield"=> "required|numeric",
            "InitiatorIFSC"=> "required",
            "InitiatorBankAccountNumber"=> "required",
            "InitiatorDpId"=> "required",
            "InitiatorClientID"=> "required",
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }
    public function ValidatePaymentRequest($request){
        $validator = Validator::make($request->all(), [
            "trade_date"=>"required|date_format:d/m/Y",
            "order_no"=>"required|numeric",
            "user_id"=>"required|numeric",
            "trade_no"=>"required|numeric"
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    //==================== For Report validation ==================================

    public function ValidateOrderReport($request){
        $validator = Validator::make($request->all(), [
            //"tpa_code"=>"required",
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    public function ValidateTradeReport($request){
        $validator = Validator::make($request->all(), [
            //"tpa_code"=>"required",
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    public function ValidateTradeHistoryReport($request){
        $validator = Validator::make($request->all(), [
            //"tpa_code"=>"required",
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }

    public function ValidateModifyOrder($request){
        $validator = Validator::make($request->all(), [
            "order_id"=>"required",
            "user_id"=>"required",
            "user_ucc"=>"required",
            "tpa_id"=>"required",
            "order_qty"=>"required",
            "category_id"=>"required",
            "clean_price"=>"required",
            "accrued_int"=>"required",
            "dirty_price"=>"required",
            "total_consideration"=>"required",
            "ISINNumber"=> "required",
            "Yield"=> "required|numeric",
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return null;
    }
}
