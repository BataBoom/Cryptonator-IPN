require __DIR__ . '/../config/config.php';

use Curl\Curl;
//https://github.com/php-curl-class/php-curl-class

function newInvoice($username, $orderID, $amount, $promoCode) {
    global $dbKey;
    global $now;
    global $thirtymins;
    global $clock;
$record = array('id' => $orderID, 'username' => $username, 'invoice_id' => '', 'created' => $now, 'expires' => $thirtymins, 'status' => 'unpaid',  'amount' => $amount, 'promo'=>$promoCode);
$curl = new Curl();
$curl->setBasicAuthentication('Authorization', $dbKey);
$curl->setHeader('Content-Type', 'application/json');
$curl->get('https://api.m3o.com/v1/db/Create', [
    'record' => $record,
    'table' => 'invoices'
    
]);
if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    $fetchResponse = $curl->response;
    $jsonEncoded = json_encode($fetchResponse);
    $grabLogin = json_decode($jsonEncoded, true);
	var_dump($curl->response);
}
$curl->close();
}

function createInvoice($name, $orderID, $amount, $promoCode){
        global $merchant_id;
        $curl = new Curl();
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $curl->setOpt(CURLOPT_NOBODY, true);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $curl->get("https://api.cryptonator.com/api/merchant/v1/startpayment?item_name=$name&order_id=$orderID&invoice_amount=$amount&item_description=$promoCode&invoice_currency=usd&merchant_id=$merchant_id");
        $new_url = $curl->effectiveUrl;
        header("Location:$new_url");
        $curl->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('order-form') === true) {
    // create validator & auto-validate required fields
    $validator = Form::validate('order-form');

    // check for errors
    if ($validator->hasErrors()) {
        $_SESSION['errors']['order-form'] = $validator->getAllErrors();
    } else {

$username = $_SESSION['username'];
$dash = $username . '-';
$orderID = uniqid($dash, false);
$amount = $_POST['depositAmount'];
$promoCode = $_POST['promo'];
$name = "Custom $"."$amount Deposit";
newInvoice("$uid", $orderID, $amount, $promoCode);

createInvoice($name, $orderID, $amount, $promoCode);
    }
}
Form::clear('order-form');
