<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** uuid2profile.php - Get profile information
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth();
$fa->checkInput();
$input = $fa->getInput(false);

$response = [];
for ($i = 0; $i < count($input); $i++) {
	$userReq = $input[$i];
	if (!$userReq || $userReq == "") $fa->errOut(400);
	if (!$user = $fa->getUserByDisplayName($userReq)) continue;
	$response[] = (object) [
		'id' => str_replace('-', '', $user['clientToken']),
		'name' => $user['displayName']
	];
}
echo json_encode($response);
?>