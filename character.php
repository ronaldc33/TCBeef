<?php

$servername = "68.178.143.47";
$username = "tri1630509080280";
$password = "L!ghthouse33";
$dbname = "tri1630509080280";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//$conn->close();

$apiKey = 'edfd82990f1b4b88860de62e60ee174e';

$ch = curl_init();

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-API-Key: ' . $apiKey));

//query numbers record for distinct membershipIds
$sql = "SELECT DISTINCT characterId, membershipId FROM numbers";
$characters = $conn->query($sql);
if ($characters->num_rows > 0) {
    // output data of each row
    while($row = $characters->fetch_assoc()) {
    	$membershipId = $row["membershipId"];
    	$characterId = $row["characterId"];
        curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/Destiny/1/Account/{$membershipId}/Character/{$characterId}/");
        // echo "http://www.bungie.net/Platform/Destiny/1/Account/{$membershipId}/Character/{$characterId}/";
        $character = json_decode(curl_exec($ch));
        $class = $character->Response->data->characterBase->classType;
        switch ($class) {
        case 0:
        	$className = 'Titan';
        	break;
        case 1:
        	$className = 'Hunter';
        	break;
        case 2:
        	$className = 'Warlock';
        	break;
        }  
        $sql = "SELECT 'X' FROM characters where characterId = {$characterId}";
        $result = $conn->query($sql);
        if ($result->num_rows == 0){
        	$sql = "INSERT INTO characters  (characterId,
        	membershipId ,
        	class) 
        	VALUES ('{$characterId}','{$membershipId}','{$className}')";
        	if ($conn->query($sql) === TRUE) {
        		//echo "New record created successfully";
        	} else {
        		echo "Error: " . $sql . "<br>" . $conn->error;
        	} 
        	
        }
    }
}
else {
	echo "0 results";
}
?>