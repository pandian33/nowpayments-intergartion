<?php
include_once "../shop/database.php";
include_once "nowPayment.php";
include "constantsNowPayments.php";

$quicker = new Payments();

$quicker->setValues($connection, $payMainUrl, $nowPaymentToken, $userEmail, $password);
$action = $_REQUEST['action'];
switch($action){
    case "generate-token":
        $quicker->generateToken();
        break;
    case "create-plan":
        $quicker->createPlan();
        break;
    case "update-plan":
        $quicker->updatePlan();
        break;
    default:
        $helper->finalResponse(false,404,"No Results");
        break;
}
?>