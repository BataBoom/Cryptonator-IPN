<?php
require __DIR__ . '/../config/config.php';
/*
UPDATE PAYMENT RECORDS & ADD BALANCE TO USER IF USER PAID 
* Cron this page to check for payments /usr/bin/php /var/www/yourdomain/includes/crons/activatePayment.php * * * * *
* use this file/URL in your Cryptonator Settings
*/


 /* Payment Webhook, Save Data */
ini_set('enable_post_data_reading', true);
$data = array('orderID'=> $_REQUEST['order_id'], 'invoiceID'=> $_REQUEST['invoice_id'], 'created'=> $_REQUEST['created'], 'expires'=>$_REQUEST['expires'], 'amount'=> $_REQUEST['amount'],'ip'=>$_REQUEST['ip'],'coin'=>$_REQUEST['coin'],'status'=>$_REQUEST['status']);


$oid = $data['orderID'];
$stat = $data['status'];
$made = $data['created'];
$today = date("Y-m-d H:i:s");
$hours6 = date('Y-m-d H:i:s', strtotime( $today . " -6 hours"));

if ($stat === "paid" && $made >= $hours6){

    //Save a Record first
    $inp = file_get_contents('../logs/fetchPayments.json');
    $tempArray = json_decode($inp);
    array_push($tempArray, $data);
    $jsonData = json_encode($tempArray);
    file_put_contents('../logs/fetchPayments.json', $jsonData);


    //Proceed
$fetchPayment = readW("id", "$oid", "invoices", "1");
$orderID = array_column($fetchPayment, 'id');
$username = array_column($fetchPayment, 'username');
$status = array_column($fetchPayment, 'status');
$created = array_column($fetchPayment, 'created');
$amount = array_column($fetchPayment, 'amount');
$promo = array_column($fetchPayment, 'promo');


if ($promo === "BATABOOM" or "bataboom"){
//add 20 percent
$amt = 1.2 * $amount[0];
//finish
$currentBalance = fetchBalance("$username[0]");
$update = $currentBalance + $amt;
updateBalance("$username[0]", "$update");
creditInvoice("$orderID[0]");

  } else{
    $amt = $amount[0];
    $currentBalance = fetchBalance("$username[0]");
    $update = $currentBalance + $amt;
    updateBalance("$username[0]", "$update");
    creditInvoice("$orderID[0]");
  }
} else {

        //Save Record of hackers
    $inp = file_get_contents('../logs/paymentRequests.json');
    $tempArray = json_decode($inp);
    array_push($tempArray, $data);
    $jsonData = json_encode($tempArray);
    file_put_contents('../logs/paymentRequests.json', $jsonData);


}
 
  
?>
