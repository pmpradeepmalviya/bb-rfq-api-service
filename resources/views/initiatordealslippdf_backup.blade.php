
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/x-icon" href="">
    <title>Thank YOU</title>
    <!-- <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800' rel='stylesheet' type='text/css'>
   
    -->
</head>

<body style="padding:10px; margin:0; font-weight: 500; font-size: 16px; line-height: 26px; font-family: 'Open Sans', helvetica, sans-serif; color: #000000;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
        <tr>
            <td valign="top" width="100%">
                <!-- SET: CONTAINER TABLE -->
                <table width="90%" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                    <tr>
                        <td  style="padding: 50px 20px;width: 60%;">
                            <div class="confirmation_slip" style="display: flex; align-items: center; position: relative;  max-width: 345px;">
                                <img src="images/confirmation_slip.svg" alt="" class="img_1" style="position: absolute; top: -41px; left: 0; width: 80px;">
                                <span style="border: 1px solid #f5821f; border-left: none;  padding: 12px 35px; font-size: 21px; border-radius: 26px; font-weight: 600;  margin-left: 45px;  border-radius: 30px;">Deal Confirmation Slip</span>
                                <img src="images/green_checkmark.svg" alt="" class="img_2" style="position: absolute;  right: -8px; width: 30px; top: -9px;">
                            </div>
                        </td>
                        <td style="padding: 50px 20px;;width: 40%" align="right">
                            <div class="logo">
                                <img src="images/logo.png" alt="" style="width: 169px;  height: 73px;">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="10" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%" style="overflow: hidden;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1.4px solid #0e0e0e;border-radius: 15px;overflow: hidden;">
                                <tr>
                                    <td rowspan="2" style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;overflow: hidden;">
                                        Personal  Information
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">Name</td>
                                    <td colspan="3" style="width:60%; padding: 10px 15px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['name']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;">
                                        UCC
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e">{{$initiator_details['ucc']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e"></td>
                                    <td style="width: 20%; padding: 10px 15px;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="20" colspan="2" width="100%"></td>
                    </tr>

                    <tr>
                        <td colspan="2" width="100%">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1.4px solid #0e0e0e;border-radius: 15px;overflow: hidden;">
                                <tr>
                                    <td rowspan="3" style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                        Order Details
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">Order Type</td>
                                    <td colspan="3" style="width:60%; padding: 10px 15px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['order_type']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                        Order No.
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['order_no']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">Trade No</td>
                                    <td style="width: 20%; padding: 10px 15px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['trade_no']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width: 20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;">
                                        Order Date & Time
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e">{{$initiator_details['order_dtm']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e">Trade Date & Time</td>
                                    <td style="width: 20%; padding: 10px 15px;">{{$initiator_details['trade_dtm']}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="20" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1.4px solid #0e0e0e;border-radius: 15px;overflow: hidden;">
                                <tr>
                                    <td rowspan="3" style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                        Security Details
                                    </td>
                                    <td style="width:20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">ISIN</td>
                                    <td  style="width: 20%; padding: 10px 15px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">{{$initiator_details['isin']}}</td>
                                    <td  style="width: 20%; padding: 10px 15px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">Face Value</td>
                                    <td  style="width: 20%; padding: 10px 15px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['face_value']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width: 20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                        Coupon %
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['coupon_rate']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">Maturity Date</td>
                                    <td style="width: 20%; padding: 10px 15px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['maturity_date']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;">
                                        Security Name
                                    </td>
                                    <td colspan="3" style=" padding: 10px 15px;">{{$initiator_details['security_name']}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="20" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1.4px solid #0e0e0e;border-radius: 15px;overflow: hidden;">
                                <tr>
                                    <td rowspan="4" style="width:20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;">
                                        Transaction Details
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">Clean Price</td>
                                    <td  style="width:20%; padding: 10px 15px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">{{$initiator_details['clean_price']}}</td>
                                    <td  style="width: 20%; padding: 10px 15px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">Accrued Interest</td>
                                    <td  style="width:20%; padding: 10px 15px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['accrued_interest']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                        Dirty Price
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['dirty_price']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">Yield</td>
                                    <td style="width: 20%; padding: 10px 15px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['yield']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">
                                        Stamp Duty
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['stamp_duty']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;border-bottom: 1px solid #0e0e0e;">Quantity</td>
                                    <td style="width: 20%; padding: 10px 15px;border-bottom: 1px solid #0e0e0e;">{{$initiator_details['quantity']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;">
                                        Total Consideration
                                    </td>
                                    <td colspan="3" style="padding: 10px 15px;">{{$initiator_details['total_consideration']}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="50" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1.4px solid #0e0e0e;border-radius: 15px;overflow: hidden;">
                                <tr>
                                    <td rowspan="2" style="width:20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; background-color: #facea1; box-shadow: inset 0 0 15px #f8a100b3, inset 0 0 0 #f8a100b3;overflow: hidden">
                                        <b>Settlement Details</b>
                                    </td>
                                    <td style="width:20%; padding: 10px 15px; border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e;">
                                        <b>Settlement  No.</b>
                                    </td>
                                    <td  style="width: 20%; padding: 10px 15px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">{{$initiator_details['settlement_no']}}</td>
                                    <td  style="width: 20%; padding: 10px 15px;border-right: 1px solid #0e0e0e; border-bottom: 1px solid #0e0e0e">
                                        <b>Settlement Date</b>
                                    </td>
                                    <td  style="width: 20%; padding: 10px 15px; border-bottom: 1px solid #0e0e0e">{{$initiator_details['settlement_date']}}</td>
                                </tr>
                                <tr>
                                    <td rowspan="" style="width:20%;padding: 10px 15px; border-right: 1px solid #0e0e0e;">
                                        <b>Settlement Type</b>
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;">{{$initiator_details['settlement_type']}}</td>
                                    <td style="width: 20%; padding: 10px 15px; border-right: 1px solid #0e0e0e;">
                                        <b>Settlement Amount</b>
                                    </td>
                                    <td style="width: 20%; padding: 10px 15px;">{{$initiator_details['settlement_amount']}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="20" colspan="2" width="100%"></td>
                    </tr>

                    <tr>
                        <td colspan="2" style="width:100%;padding: 10px 0;">
                            <p style="font-size: 16px;font-weight:400;"> <b>DISCLAIMER:</b> This is a computer generated Deal Confirmation Slip &amp; does not require a signature in order to be considered valid. This document gives details of the deal confirmed as mentioned above. In case of any objection with respect to any of the details mentioned here, request you to kindly communicate the same within 24 hours of the receipt of the deal slip. Any changes communicated thereafter will not be honoured. The Contract Note for the above mentioned deal will be sent to you on your registered email id by the end of the day..</p>
                        </td>
                    </tr>
                    <tr>
                        <td height="10" colspan="2" width="100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="width:100%;padding: 10px 0;font-weight:400;">
                                Complex, Bandra East Mumbai - 400051 | Corporate Office: 206, Balarama Co-Op Housing Society Ltd, Bandra Kurla Complex, Bandra East, Mumbai - 400051 CIN Number: U67100MH2021PTC364337 1 SEBI Registration Number: INZ000303236 - 31st Dec 2021 | NSE Membership No. 90247 | BSE Membership No. 6768 | CDSL DP No. IN-DP-700-2022 7th July 2022 Email Address: <a href="connect@bondbazaar.com" style="color: #f5821f;">connect@bondbazaar.com</a> 
                                I Telephone Number: 022-35121163/64 | <a href="Website: www.bondbazaar.com" style="color: #f5821f;">Website: www.bondbazaar.com</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td height="10" width="100%" colspan="2"></td>
                    </tr>
                </table>
                <!-- END: CONTAINER TABLE -->
            </td>
        </tr>
    </table>
</body>

</html>