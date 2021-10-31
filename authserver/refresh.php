<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** refresh.php - /refresh Endpoint
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('refresh');
$fa->checkInput();
$input = $fa->getInput(true);

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($input) . "\n\n");
$cToken = htmlspecialchars($input->clientToken);
$aToken = htmlspecialchars($input->accessToken);
if (!$user = $fa->getUserByAccessToken($aToken) || $cToken != $user['clientToken'] || $aToken != $user['accessToken']) $fa->errOut(403);

$aToken = $fa->genUUID();
$fa->setUser($userName, ['accessToken' => $aToken]);

$response = (object) [
	'accessToken' => $aToken,		// hexadecimal random access token
	'clientToken' => $cToken,
	'selectedProfile' => [
		'id' => str_replace('-', '', $cToken),	// uuid without dashes
		'name' => $user['displayName']			// player name
	],
];
if (isset($input->requestUser) && $input->requestUser) {
	$response->user = [
		'id' => str_replace('-', '', $cToken),				// hexadecimal user identifier
		'properties' => [[
			'name' => 'preferredLanguage',
			'value' => 'en'
		]]
	];
};

fwrite($logHandle, "Returned payload:\n" . json_encode($response));
fclose($logHandle);
echo json_encode($response);
?>