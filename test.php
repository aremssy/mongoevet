<?php
// http://3.135.63.188/mongo/
// http://ec2-3-135-63-188.us-east-2.compute.amazonaws.com/mongo/

// This is the first request. Note how we start the response with CON
$response =  "CON Welcome to FG RRR.";
$response .=    "\nSelect Gender To Proceed Or Exit";
$response .=    "\n1. Male";
$response .=    "\n2. Female";
$response .=    "\n3. Exit";

header('Content-type: text/plain');
echo $response;