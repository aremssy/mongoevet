<?php
// Report all errors
error_reporting(E_ALL);
// Date
$date = date("Y-m-d");

//1. Reads the variables sent via POST from our gateway
// $sessionId   = (isset($_REQUEST["sessionId"])) ? $_REQUEST["sessionId"] : "";
// $serviceCode = (isset($_REQUEST["serviceCode"])) ? $_REQUEST["serviceCode"] : "";
// $networkCode = (isset($_REQUEST["networkCode"])) ? $_REQUEST["networkCode"] : "";
// $phoneNumber = (isset($_REQUEST["phoneNumber"])) ? $_REQUEST["phoneNumber"] : "";
// $status = (isset($_REQUEST["status"])) ? $_REQUEST["status"] : "";
// $cost = (isset($_REQUEST["cost"])) ? $_REQUEST["cost"] : "";
// $durationInMillis = (isset($_REQUEST["durationInMillis"])) ? $_REQUEST["durationInMillis"] : "";
// $hopsCount = (isset($_REQUEST["hopsCount"])) ? $_REQUEST["hopsCount"] : "";
// $input = (isset($_REQUEST["input"])) ? $_REQUEST["input"] : "";
// $lastAppResponse = (isset($_REQUEST["lastAppResponse"])) ? $_REQUEST["lastAppResponse"] : "";
// $errorMessage = (isset($_REQUEST["errorMessage"])) ? $_REQUEST["errorMessage"] : "";
// $ussd_string = (isset($_REQUEST["text"])) ? $_REQUEST["text"] : "";

// checking whether file exists or not
$file_pointer = '/var/www/html/mongo/events/results.json';
 
if (file_exists($file_pointer)) 
{
	// open the file (take care to not use "w" mode)
	$f = fopen($file_pointer, 'r+');
	// obtain an exlusive lock (may suspend the process)
	if (flock($f, LOCK_EX)) {

			$inp = file_get_contents('results.json');
			$tempArray = json_decode($inp, true);
			// array_push($tempArray, $_POST);
			$tempArray[] = $_POST;
			$jsonData = json_encode($tempArray);
			file_put_contents('results.json', $jsonData);
	    flock($f, LOCK_UN);
	}
	// don't perform any write operation on $f here
	fclose($f);
}
else 
{
	$jsonData = json_encode($_POST); 
	$fp = file_put_contents( 'results.json', $jsonData );
}
// header('Content-type: text/plain');
// var_dump($fp);