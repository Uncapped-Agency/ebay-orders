<?php
session_start();
//add configs
require_once 'config.php';
//add classes
require_once __DIR__ .'/lib/EbayRepository.php';
require_once __DIR__ .'/lib/Curl.php';
require_once __DIR__ .'/lib/Database.php';

$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$ebay = new EbayRepository($db);

//callback https://test.com/path?redirect=ebay_callback
if(isset($_GET['redirect']) && $_GET['redirect']=='ebay_callback'){
	//get && set access token
	$ebay->grantFlowCallback();
}
// https://developer.ebay.com/api-docs/sell/fulfillment/resources/order/methods/getOrders#response.href
print_r($ebay->getOrders());



