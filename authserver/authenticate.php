<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** authenticate.php - /authenticate Endpoint
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('authenticate');
$fa->checkInput();
$input = $fa->getInput(true);

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($input) . "\n\n");
$userName = htmlspecialchars($input->username);

if (!$user = $fa->getUserByName($userName)) $fa->errOut(403);

// Password verification
$inputPass = $fa->hash($input->password);
if (!hash_equals($user['password'], $inputPass)) $fa->errOut(401);
$cToken = false;
$aToken = false;
if (isset($user['clientToken']) && !empty($user['clientToken'])) {
	$cToken = $user['clientToken'];
	$aToken = $fa->genUUID();
	$fa->setUser($userName, ['accessToken' => $aToken]);
} else if (isset($input->clientToken)) {
	$cToken = $input->clientToken;
	$aToken = $fa->genUUID();
	$fa->setUser($userName, ['clientToken' => $cToken, 'accessToken' => $aToken]);
} else {
	$cToken = $fa->genUUID();
	$aToken = $fa->genUUID();
	$fa->setUser($userName, ['clientToken' => $cToken, 'accessToken' => $aToken]);
}

$response = (object) [
	'accessToken' => $aToken,		// hexadecimal random access token
	'clientToken' => $cToken
];
if (isset($input->agent)) {
	$response->availableProfiles = [[
		'id' => '',			// hexadecimal profile identifier
		'name' => '',		// player name
		'legacy' => false
	]];
	$response->selectedProfile = [
		'id' => str_replace('-', '', $cToken),	// uuid without dashes
		'name' => $user['displayName'],			// player name
		'legacy' => false
	];
};
if (isset($input->agent) && isset($input->requestUser) && $input->requestUser) {
	$response->user = [
		'id' => str_replace('-', '', $cToken),		// hexadecimal user identifier
		'properties' => [[
			'name' => 'preferredLanguage',
			'value' => 'en'
		]]
	];
};

fwrite($logHandle, "Returned payload:\n" . json_encode($response));
fclose($logHandle);
// This errs the launcher, but still prevents prevents it from freezing when it doesn't get a response
echo json_encode($response);
?>