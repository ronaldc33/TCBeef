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
$sql = "SELECT DISTINCT membershipId FROM numbers";
$guardians = $conn->query($sql);
if ($guardians->num_rows > 0) {
    // output data of each row
    while($row = $guardians->fetch_assoc()) {
    	$membershipId = $row["membershipId"];
        curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/User/GetMembershipsById/{$membershipId}/1/");
        $gamerTags = json_decode(curl_exec($ch));
        $tag = $gamerTags->Response->destinyMemberships[0]->displayName;
        
        $sql = "SELECT 'X' FROM guardians where membershipId = {$membershipId}";
        $result = $conn->query($sql);
        if ($result->num_rows == 0){
        	$sql = "INSERT INTO guardians  (
        	membershipId ,
        	gamerTag) 
        	VALUES ('{$membershipId}','{$tag}')";
        	if ($conn->query($sql) === TRUE) {
        		//echo "New record created successfully";
        	} else {
        		echo "Error: " . $sql . "<br>" . $conn->error;
        	} 
        	
        }
        else { $sql = "UPDATE guardians  SET 
        	gamerTag ='{$tag}' where membershipId = '{$membershipId}'";
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