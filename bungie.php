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
//curl_setopt($ch, CURLOPT_URL, 'https://www.bungie.net/platform/Destiny/Manifest/InventoryItem/1274330687/');
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-API-Key: ' . $apiKey));

// curl_setopt($ch, CURLOPT_URL, 'https://www.bungie.net/Platform/Destiny/1/Stats/GetMembershipIdByDisplayName/The Fokused 1/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-API-Key: ' . $apiKey));

//$json = json_decode(curl_exec($ch));

//$membershipId = $json->Response;
// echo "The Fokused 1 Membership ID {$membershipId}";

//curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/User/GetBungieAccount/{$membershipId}/1/");

$json = json_decode(curl_exec($ch));

//echo " groupid ";
$groupId = '216080';
//echo "Clan (groupId):{$groupId}<br>";
//$characterId = $json->Response->destinyAccounts[0]->characters[0]->characterId;
//echo "<br>{$characterId}";
//echo $json->Response->destinyAccounts[0]->characters[0]->characterClass->className;
//echo "Error";
//echo $json->ErrorStatus;

//www.bungie.net/Platform/Destiny/Stats/{membershipType}/{destinyMembershipId}/{characterId}/

//get my clan id
///Platform/User/GetBungieAccount/{membershipId}/{membershipType}/

//get membership IDs for clan members:
curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/Group/{$groupId}/ClanMembers/?lc=en&fmt=true&currentPage=1&platformType=1");

$json = json_decode(curl_exec($ch));
$clanMembers = sizeof($json->Response->results);
for( $i = 0; $i<$clanMembers; $i++ ) {
	$membershipId = $json->Response->results[$i]->destinyUserInfo->membershipId;
	//for testing only pull The Fokused 1
	//$membershipId = '4611686018430080965';
	//show displayNames for debugging purposes
	//echo "<br>{$json->Response->results[$i]->destinyUserInfo->displayName}   ";
	//echo $membershipId;
	//echo "https://www.bungie.net/Platform/User/GetBungieAccount/{$membershipId}/1/";
	curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/Destiny/1/Account/{$membershipId}/");
	$characterSet = json_decode(curl_exec($ch));
	$error = $characterSet->ErrorCode;
	//print the entire JSON response
	//echo curl_exec($ch);
	//echo "Error Code:{$error}";
	if ($error = 1) {
		$characters = sizeof($characterSet->Response->data->characters);
		for( $j = 0; $j<$characters; $j++ ) {
			$characterId = $characterSet->Response->data->characters[$j]->characterBase->characterId;
			$page = 0;
	        $totalPMs = 0;
	        $numPMs = 1;
			//echo "<br>CharacterID:{$characterId}";
			//increment the page if last page was full
			//while ($numPMs > 0) {
				//echo "Page:{$page}";
				curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/Destiny/Stats/ActivityHistory/1/{$membershipId}/{$characterId}/?mode=PrivateMatchesAll&page={$page}&count=25");
				$privateMatches = json_decode(curl_exec($ch));
				$numPMs = sizeof($privateMatches->Response->data->activities);
				//$totalPMs = $totalPMs + $numPMs;
				//echo "total games:{$totalPMs}<br>";
				for( $k = 0; $k<$numPMs; $k++ ) {
					$activityInstanceId = $privateMatches->Response->data->activities[$k]->activityDetails->instanceId;
					//echo $activityInstanceId;
					//echo "<br>";
					curl_setopt($ch, CURLOPT_URL, "https://www.bungie.net/Platform/Destiny/Stats/PostGameCarnageReport/{$activityInstanceId}/"); 
					$pgcr = json_decode(curl_exec($ch));
					$mode = $pgcr->Response->data->activityDetails->mode;
					$map = $pgcr->Response->data->activityDetails->referenceId;
					$date = date ('Y-m-d H:i:s',strtotime($pgcr->Response->data->period));
					$players = sizeof($pgcr->Response->data->entries);
					//echo "<br> number of players: {$players} <br>";
					for( $l = 0; $l<$players; $l++ ){
						$membershipId = $pgcr->Response->data->entries[$l]->player->destinyUserInfo->membershipId;
						
						$sql = "SELECT 'X' FROM numbers where membershipId = {$membershipId} and instanceId = {$activityInstanceId}";
						//echo "SELECT 'X' FROM numbers where membershipId = {$membershipId} and instanceId = {$activityInstanceId}<br>";		
						
						$result = $conn->query($sql);
						if ($pgcr->Response->data->entries[$l]->values->completed->basic->value == 1){
							if ($result->num_rows == 0) {
								$characterId = $pgcr->Response->data->entries[$l]->characterId;
								$team = $pgcr->Response->data->entries[$l]->values->team->basic->value;
								$won = $pgcr->Response->data->entries[$l]->standing;
								$score = $pgcr->Response->data->entries[$l]->values->score->basic->value;
								$kills = $pgcr->Response->data->entries[$l]->values->kills->basic->value;
								$deaths = $pgcr->Response->data->entries[$l]->values->deaths->basic->value;
								$assists = $pgcr->Response->data->entries[$l]->values->assists->basic->value;
								$kd = $pgcr->Response->data->entries[$l]->values->killsDeathsRatio->basic->value;
								$kda = $pgcr->Response->data->entries[$l]->values->killsDeathsAssists->basic->value;
								$gun = $pgcr->Response->data->entries[$l]->extended->weapons[0]->referenceId;
								
								$sql = "INSERT INTO numbers  (
								instanceId ,
								membershipId ,
								characterId ,
								map ,
								mode ,
								date ,
								team ,
								won ,
								score ,
								kills ,
								deaths,
								assists ,
								killsDeathsRatio ,
								killsDeathsAssists ,
								bestweapon) 
								VALUES ('{$activityInstanceId}','{$membershipId}','{$characterId}','{$map}',' {$mode}',' {$date}',' {$team}',' {$won}',' {$score}',' {$kills}',' {$deaths}',' {$assists}',' {$kd}',' {$kda}','{$gun}')";
								//echo $sql;
								if ($conn->query($sql) === TRUE) {
									//echo "New record created successfully";
								} else {
									echo "Error: " . $sql . "<br>" . $conn->error;
								} 
								//echo curl_exec($ch);
							}
						}					
					}
				//}
				//$page++;
			}
		}
	}
}

?>