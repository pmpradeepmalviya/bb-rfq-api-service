
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/x-icon" href="">
    <title>Thank YOU</title>
    <!-- <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800' rel='stylesheet' type='text/css'>-->
</head>

<body style="margin:0; font-weight: 400; font-size: 14.2px; font-family: Segoe UI; color: #000000;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
        <tr>
        <img src="images/logo.png" alt="" style="width: 170%;height:50%;position:absolute;z-index:-1;opacity:0.1;bottom:-37px;right:-760px;">
            <td valign="top" width="100%">
                <!-- SET: CONTAINER TABLE -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                    <tr>
                        <!-- <td  style="padding: 50px 20px;width: 60%;">
                            <div class="confirmation_slip" style="display: flex; align-items: center; position: relative;  max-width: 345px;">
                                <img src="images/confirmation_slip.svg" alt="" class="img_1" style="position: absolute; top: -41px; left: 0; width: 80px;">
                                <span style="border: 1px solid #f5821f; border-left: none;  padding: 12px 35px; font-size: 21px; border-radius: 26px; font-weight: 600;  margin-left: 45px;  border-radius: 30px;">Deal Confirmation Slip</span>
                                <img src="images/green_checkmark.svg" alt="" class="img_2" style="position: absolute;  right: -8px; width: 30px; top: -9px;">
                            </div>
                        </td> -->
                        <td style="width: 60%;font-size: 18px;position: relative;">
                        <img src="images/confirmation_slip.svg" alt="" style="width: 80px;height:90px;margin-top: -30px;">
                        <span style="margin-top: -20px;
                                    position: absolute;
                                    top: 24px;
                                    left: 80px;
                                    border: 1px solid #f5821f;
                                    border-left: none;
                                    border-top-right-radius: 30px !important;
                                    border-bottom-right-radius: 30px !important;
                                    padding: 11px 11px 11px 20px;
                                    font-size: 16px;
                                    border-radius: 26px;font-weight: 600;
                                    border-top-left-radius: 0px !important;
                                    margin-left: -32px;border-bottom-left-radius: 0px !important;">Deal Confirmation Slip</span> 
                                    <img src="images/green_checkmark.png" alt="" 
                                    style="width: 24px;position: absolute;bottom: 0;left: 220px;top: -2px;"></td>
                        <td style="width: 40%;" align="right">
                            <div class="logo" style="margin-top: -27px;">
                                <img src="images/logo.png" alt="" style="width: 145px;height: 57px;">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="7" colspan="2" width="100%"></td>
                    </tr>
                    <tr style="">
                        <td colspan="2" width="100%" style="overflow: hidden;">
                        <div style="border: 1.4px solid #0e0e0e;border-radius:15px;overflow: hidden;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="overflow: hidden;">
                                <tr>
                                    <td rowspan="2" style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                       <b> Personal  Information</b>
                                    </td>
                                    <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;"><b>Name</b></td>
                                    <td colspan="3" style="width: 60%; padding: 5px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['name']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;">
                                        <b>UCC</b>
                                    </td>
                                    <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e"><b>{{$initiator_details['ucc']}}</b></td>
                                    <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e"><b>DP ID</b></td>
                                    <td style="width: 20%; padding: 5px;">{{$initiator_details['dpid']}}</td>
                                </tr>
                            </table>
                        </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="11" colspan="2" width="100%"></td>
                    </tr>

                    <tr>
                        <td colspan="2" width="100%">
                            <div style="border: 1.4px solid #0e0e0e;border-radius:15px;overflow: hidden;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                    <tr>
                                        <td rowspan="3" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                           <b> Order Details</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;"><b>Order Type</b></td>
                                        <td colspan="3" style="width: 60%; padding: 5px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['order_type']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width:20%;padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                            <b>Order No.</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['order_no']}}</td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;"><b>Trade No</b></td>
                                        <td style="width: 20%; padding: 5px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['trade_no']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Order Date & Time</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e">{{$initiator_details['order_dtm']}}</td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e"><b>Trade Date & Time</b></td>
                                        <td style="width: 20%; padding: 5px;">{{$initiator_details['trade_dtm']}}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="11" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%">
                            <div style="border: 1.4px solid #0e0e0e;border-radius:15px;overflow: hidden;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                    <tr>
                                        <td rowspan="3" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                            <b>Security Details</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;"><b>ISIN</b></td>
                                        <td  style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">{{$initiator_details['isin']}}</td>
                                        <td  style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e"><b>Face Value</b></td>
                                        <td  style="width: 20%; padding: 5px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['face_value']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                            <b>Coupon %</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['coupon_rate']}}</td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;"><b>Maturity Date</b></td>
                                        <td style="width: 20%; padding: 5px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['maturity_date']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Security Name</b>
                                        </td>
                                        <td colspan="3" style=" padding: 5px;">{{$initiator_details['security_name']}}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="11" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%">
                            <div style="border: 1.4px solid #0e0e0e;border-radius:15px;overflow: hidden;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                    <tr>
                                        <td rowspan="4" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                            <b>Transaction Details</b>
                                        </td>
                                        <td  style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;"><b>Clean Price</b></td>
                                        <td  style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">{{$initiator_details['clean_price']}}</td>
                                        <td  style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e"><b>Accrued Interest</b></td>
                                        <td  style="width: 20%; padding: 5px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['accrued_interest']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                            <b>Dirty Price</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['dirty_price']}}</td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;"><b>Yield</b></td>
                                        <td style="width: 20%; padding: 5px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['yield']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                            <b>Stamp Duty</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['stamp_duty']}}</td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;"><b>Quantity</b></td>
                                        <td style="width: 20%; padding: 5px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['quantity']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Total Consideration</b>
                                        </td>
                                        <td colspan="3" style="padding: 5px;">{{$initiator_details['total_consideration']}}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="11" colspan="2" width="100%"></td>
                    </tr>
                    <tr style="">
                        <td colspan="2" width="100%;">
                            <div style="border: 1.4px solid #0e0e0e;border-radius:15px;overflow: hidden;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                    <tr>
                                        <td rowspan="2" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;overflow: hidden">
                                            <b>Settlement Details</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                            <b>Settlement  No.</b>
                                        </td>
                                        <td  style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">{{$initiator_details['settlement_no']}}</td>
                                        <td  style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">
                                            <b>Settlement Date</b>
                                        </td>
                                        <td  style="width: 20%; padding: 5px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['settlement_date']}}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Settlement Type</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;">{{$initiator_details['settlement_type']}}</td>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Settlement Amount</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; color: #FF0000;"><b>{{$initiator_details['settlement_amount']}}</b></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="11" colspan="2" width="100%"></td>
                    </tr>
                    <tr style="">
                        <td colspan="2" width="100%;">
                            <div style="border: 1.4px solid #0e0e0e;border-radius:15px;overflow: hidden;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                    <tbody><tr>
                                        <td rowspan="8" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;overflow: hidden">
                                            <b>For Fund/Security Transfer</b>
                                        </td>
                                        <td colspan="2" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                            <b>For Fund Transfer</b>
                                        </td>
                                        <td colspan="2" style="width: 20%; padding: 5px; border-bottom: 1px solid #0e0e0e"> <b>For Bond Transfer</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                            <b>Bank Name</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">HDFC Bank LTD</td>
                                        <td style="width: 20%; padding: 5px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">
                                            <b>Market Type</b>
                                        </td>
                                        <td style="width: 20%; padding: 5px; border-bottom: 1px solid #0e0e0e">ICDM ( T + 1 )</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;" rowspan="3">
                                            <b>Beneficiary Name</b>
                                        </td>
                                        <td style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;" rowspan="3">ICCL</td>
                                        <td style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                            <b>CM Name</b>
                                        </td>
                                        <td style="width: 20%;padding: 5px; border-bottom: 1px solid #0e0e0e;">ICCL</td>
                                    </tr>
                                    <tr> 
                                        <td style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;" rowspan="2">
                                            <b>CM ID</b>
                                        </td>
                                        <td style="width: 20%;padding: 5px;  border-bottom: 1px solid #0e0e0e;">CDSL-999</td>
                                    </tr><tr>
                                        
                                        
                                        
                                        <td style="width: 20%;padding: 5px; border-bottom: 1px solid #0e0e0e;">NSDL-IN619994</td>
                                    </tr><tr>
                                        <td rowspan="2" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                            <b>Bank IFSC Code</b>
                                        </td>
                                        <td rowspan="2" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">HDFC00000060</td>
                                        <td rowspan="2" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                            <b>Client ID</b>
                                        </td>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-bottom: 1px solid #0e0e0e;">CDSL-11000029630</td>
                                    </tr>
                                    
                                <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-bottom: 1px solid #0e0e0e;">NSDL-100000010</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="" style="width: 20%;padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Bank Account Number</b>
                                        </td>
                                        <td rowspan="" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;">6746535467365423</td>
                                        <td rowspan="" style="width: 20%; padding: 5px; border-right: 1px solid #0e0e0e;">
                                            <b>Settlement No</b>
                                        </td>
                                        <td rowspan="" style="width: 20%; padding: 5px;">9876545</td>
                                    </tr></tbody>    
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="5" colspan="2" width="100%"></td>
                    </tr>

                    <tr>
                        <td colspan="2" style="width: 100%;">
                            <p style="font-size: 12px;font-weight:400;font-family: Segoe UI;"> <b>DISCLAIMER:</b> This is a computer generated Deal Confirmation Slip &amp; does not require a signature in order to be considered valid. This document gives details of the deal confirmed as mentioned above. In case of any objection with respect to any of the details mentioned here, request you to kindly communicate the same within 24 hours of the receipt of the deal slip. Any changes communicated thereafter will not be honoured. The Contract Note for the above mentioned deal will be sent to you on your registered email id by the end of the day..</p>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td height="10" colspan="2" width="100%"></td>
                    </tr> -->
                    <tr>
                        <td colspan="2" style="width:100%;">
                            <p style="font-size: 12px;font-weight:400;font-family: Segoe UI;"> <b>BONDBAZAAR SECURITIES PRIVATE LIMITED</b> Registered Office: 204-205, Balarama Co-Op Housing Society Ltd, BandraKurla
                                Complex, Bandra East Mumbai - 400051 | Corporate Office: 206, Balarama Co-Op Housing Society Ltd, Bandra Kurla Complex, Bandra East, Mumbai - 400051 CIN Number: U67100MH2021PTC364337 1 SEBI Registration Number: INZ000303236 - 31st Dec 2021 | NSE Membership No. 90247 | BSE Membership No. 6768 | CDSL DP No. IN-DP-700-2022 7th July 2022 Email Address: <a href="connect@bondbazaar.com" style="color: #f5821f;">connect@bondbazaar.com</a> 
                                I Telephone Number: 022-35121163/64 | <a href="Website: www.bondbazaar.com" style="color: #f5821f;">Website: www.bondbazaar.com</a>
                            </p>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td height="10" width="100%" colspan="2"></td>
                    </tr> -->
                </table>
                <!-- END: CONTAINER TABLE -->
            </td>
        </tr>
    </table>
</body>

</html>