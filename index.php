<?php
// http://3.135.63.188/mongo/
// http://ec2-3-135-63-188.us-east-2.compute.amazonaws.com/mongo/

require "vendor/autoload.php";
// $client = new MongoDB\Client('mongodb+srv://Pa22w0rd1:Pa22w0rd1@cluster0.5obu5.mongodb.net/rrrdb?retryWrites=true&w=majority');
$client = new MongoDB\Client('mongodb+srv://administrator:D1xonHou$e11@cluster0.ywjts.mongodb.net/myFirstDatabase?retryWrites=true&w=majority');

// Database name Declaration
$db = $client->rrrdb;

// User Information Table
$info = $db->info;
$collection = $db->comm;
$completed = $db->completed;

// Date
$date = date("Y-m-d H:i:s");

//1. Reads the variables sent via POST from our gateway
$sessionId   = (isset($_REQUEST["sessionId"])) ? $_REQUEST["sessionId"] : "";
$serviceCode = (isset($_REQUEST["serviceCode"])) ? $_REQUEST["serviceCode"] : "";
$phoneNumber = (isset($_REQUEST["phoneNumber"])) ? $_REQUEST["phoneNumber"] : "";
$ussd_string = (isset($_REQUEST["text"])) ? $_REQUEST["text"] : "";

// Last 5 digits plus random number makes the unique ID
$digits = intval( rand(0,99) . intval(substr($phoneNumber, 8)) . rand(0,99) );

//2. Explode the text to get the value of the latest interaction - think 1*1
$ussd_string_exploded = explode ("*",$ussd_string);
$userResponse = trim(end($ussd_string_exploded));
// $userResponse = trim(escape_string(stripslashes($userResponse)));

//3. set default level to zero
// $level = count($ussd_string_exploded) -1 ;
$level = 0 ;

//4. Get menu level from ussd_string reply
$count = count($ussd_string_exploded);

// get the first code dialled by users.
$first = reset($ussd_string_exploded);

$mobile = intval(substr($phoneNumber, 4));

// Convert First Input Respose to Integer
$community_id = intval($first);

$completed_mobile = substr($phoneNumber, 1);
// Search if the User has a session in the DB using phoneNumber
// Integer value of Mobile without +234 to be used for search

// Blocking some communities	

if (in_array($mobile, range(12600,13149)) || in_array($mobile, range(35900,38331)) || in_array($mobile, range(44000,44793)) || in_array($mobile, range(47500,47753)) || in_array($mobile, range(48000,48446)) || in_array($mobile, range(49900,52123)) || in_array($mobile, range(57500,59718)) || in_array($mobile, range(59990,61869)) || in_array($mobile, range(61990,64382)) || in_array($mobile, range(64500,65884)) || in_array($mobile, range(71700,73379)) || in_array($mobile, range(79000,81167)) || in_array($mobile, range(81500,89046)) || in_array($mobile, range(39000,40091)) || in_array($mobile, range(89100,89715)) || in_array($mobile, range(25600,27295)) || in_array($mobile, range(89800,90727)) ) {
	$level = 103;
}else{
	$record = $info->findOne( [ 'mobile' => $mobile],   ['projection' => [ 'level' => 1, 'user_id' => 1, 'LGA' => 1, '_id' => 0 ]] );
	// Set level for the first response before saving anything to DB
	// $level = (isset($record->level)) ? $record->level : 1;
		if (isset($record)) {
			$level = $record->level;
		}else{
			$level = ($count >= 2) ? 1 : 0 ;
		}


	if (in_array($level, [0, 1]) ) {	
		// Search if the User has a completed or has participated in the previous registration 
		// If user dial number out of range
		$complete_record = $completed->findOne( [ 'phone' => $completed_mobile],   ['projection' => [ 'phone' => 0, '_id' => 1 ]] );

		// Get the State, LGA, Ward and Community using the ID.
		$location = $collection->findOne( [ 'ussdid' => $community_id] ,  ['projection' =>  ['community_state' => 1, '_id' => 0]] );

		if (isset($complete_record)) {
			$level = 101;
		}

		if(!isset($location)){ 
			$level = 102;
		}

	} 


	//People that started but missed LGA and Community
	if (isset($record)) {
		$location = $collection->findOne( [ 'ussdid' => $community_id],  ['projection' =>  ['_id' => 0]]  );

		if (in_array($record->lga, ["", null]) && in_array($record->ward, ["", null]) && $record->level >= 2) {
			$result = update($info, $mobile, array( "update_at" => $date, "community_id" => $community_id, "community" => $location->Community, "ward" => $location->Ward, "lga" => $location->LGA, "state" => $location->state));
		}
	}

}

/********************************************

// Functions to call
// Update Function

********************************************/

function update($collection, $mobile, $value){
	$result = $collection->updateOne(
		array( "mobile" => $mobile),
		array( '$set' => $value)
	);
return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	// var_dump($db)."\n";
	$mobile = $_GET['mobile'];
	// $record = $info->findOne( [ 'mobile' => $mobile],  ['projection' => [ 'level' => 1, 'user_id' => 1, '_id' => 0 ]] );

	// $location = $collection->findOne( [ 'ussdid' => 32113],  ['projection' =>  ['community_state' => 1, '_id' => 0]] );

	if (in_array($mobile, range(12600,13149)) || in_array($mobile, range(35900,38331)) || in_array($mobile, range(44000,44793)) || in_array($mobile, range(47500,47753)) || in_array($mobile, range(48000,48446)) || in_array($mobile, range(49900,52123)) || in_array($mobile, range(57500,59718)) || in_array($mobile, range(59990,61869)) || in_array($mobile, range(61990,64382)) || in_array($mobile, range(64500,65884)) || in_array($mobile, range(71700,73379)) || in_array($mobile, range(79000,81167)) || in_array($mobile, range(81500,89046)) || in_array($mobile, range(39000,40091)) || in_array($mobile, range(89100,89715)) || in_array($mobile, range(25600,27295)) || in_array($mobile, range(89800,90727)) ) {
		$stage = 103;
	}else{$stage = 0;}
	var_dump($stage)."<br>";
	var_dump($mobile)."<br>";
}

switch (true) {
case (in_array($level, [0]) && $count == 1):

    // This is the first request. Note how we start the response with CON
	$response =  "CON Welcome to " .$location->community_state." FG RRR. $community_id";
	$response .=    "\nSelect Gender To Proceed Or Exit";
	$response .=    "\n1. Male";
	$response .=    "\n2. Female";
	$response .=    "\n3. Exit";
                    
    // Tracks the number with changes made and what time.
    // track($db, $phoneNumber);
    break;

    // Close/End the process 
    case ( in_array($level, [1]) && !in_array($userResponse, [1, 2]) ):   
            $response = "END Thank you for your time!";
    break;

    // Mover to stage 2 if 1.Male 2.Female is selected from Level 1 
    // Level 2 is Age collection
    case ( (in_array($level, [1]) && in_array($userResponse, [1,2])) || (in_array($level, [2]) && empty($userResponse)) || (in_array($level, [2]) && !is_numeric($userResponse)) || (in_array($level, [2]) && $count <= 1) ):
        $response = "CON Enter your Age";
        $response .= "\nOnly Numbers Allowed (E.g. 18, 24, 30, 45...)";
 
		if ($count >= 2 && in_array($level, [1]) ) {
			$gender = ($userResponse == 1) ? "Male" : "Female";
				if (!isset($record)) {

					// Get the State, LGA, Ward and Community using the ID.
					$location = $collection->findOne( [ 'ussdid' => $community_id],  ['projection' =>  ['_id' => 0]]  );

					// Insering Record  
					$info->insertOne([ "mobile" => $mobile,  "community_id" => $community_id, "level" => 2, "fname" => "", "lname" => "", "gender" => $gender, "created_at" => $date, "update_at" => $date, "user_id" => $digits	, "community" => $location->Community, "ward" => $location->Ward, "lga" => $location->LGA, "state" => $location->state, "address" => "", "completed" => "Not Completed" ]);
 
				}
			
		}

    break;

    // Mover to stage 3 if 1.Continue is selected from Level 2 
    // Level 2 is Age collection
    case ( (in_array($level, [2]) && !empty($userResponse))  || (in_array($level, [3]) && empty($userResponse)) || (in_array($level, [3]) && $count <= 1) ):
        $response = "CON Enter your First Name";

		// Updating Record  
		if ($count >= 2 && in_array($level, [2]) ) {


			// Updating Record  
			$result = update($info, $mobile, array( "update_at" => $date, "level" => 3, "age" => $userResponse));

		}

    break;

    // Mover to stage 3 if 1.Continue is selected from Level 2 
    // Level 2 is Age collection
    case ( (in_array($level, [3]) && !empty($userResponse))  || (in_array($level, [4]) && empty($userResponse)) || (in_array($level, [4]) && $count <= 1)):
        $response = "CON Enter your Last Name";

		// Updating Record  
		if ($count >= 2 && in_array($level, [3])) {
			$result = update($info, $mobile, array( "update_at" => $date, "level" => 4, "fname" => $userResponse ));
		}

    break;

    // Mover to stage 3 if 1.Continue is selected from Level 2 
    // Level 2 is Age collection
    case ( (in_array($level, [4]) && !empty($userResponse)) || (in_array($level, [5]) && empty($userResponse)) || (in_array($level, [5]) && $count <= 1)):
        $response = "CON Enter your Address";

		// Updating Record   
		if ($count >= 2 && in_array($level, [4])) { 
			$result = update($info, $mobile, array( "update_at" => $date, "level" => 5, "lname" => $userResponse ));
		}

    break;

    // Mover to stage 3 if 1.Continue is selected from Level 2 
    // Level 2 is Age collection
    case ( (in_array($level, [5]) && !empty($userResponse)) || (in_array($level, [6, 101]) && $count <= 1)):
            
            $response = "END Your details have been successfully submitted.\n";
            if (in_array($level, [5])) {
            	$response .= "Your Unique ID is: $record->user_id \n";
            }
            $response .= "Thank you for your time!\n";

		if ($count >= 2  && in_array($level, [5]) && !in_array($record->lga, ["", null])) {
			// Updating Record  
			$result = update($info, $mobile, array( "update_at" => $date, "level" => 6, "completed" => "Completed", "address" => $userResponse ) );
			$completed->insertOne([ "phone" => $completed_mobile]);
		}

    break;

    // Out of range Dial
    case ( in_array($level, [102])):
            
            $response = "END  Thank You!  \n";
            $response .= "Please Call 969 to confirm your community is part of this program.\n";
            $response .= "Thank you for your time!\n";

    break;
    // Out of range Dial
    case ( in_array($level, [103])):
            
            $response = "END The programme has been suspended for your community. Please reach out to your State Coordinator\n";
            $response .= "Thank you for your time!\n";

    break;
    default:
         // Return user to Main Menu & Demote user's level
        $response = "CON You have entered wrong input.\n";
        $response .= "Cancel To Quit\n";
}
header('Content-type: text/plain');
echo $response;