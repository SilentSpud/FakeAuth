<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** hasjoined.php - session/minecraft/hasJoined
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('hasJoined');

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($_GET) . "\n\n");
if (!$user = $fa->getUserByDisplayName($_GET['username'])) $fa->errOut(403);

$responseTextures = (object) [
	"timestamp" => (floor(microtime(true) * 1000)),
	"profileId" => str_replace('-', '', $user['clientToken']),
	"profileName" => $user['displayName'],
	"isPublic" => true,
	"textures" => (object) [],
];
if (isset($user['skinPath']) && !empty($user['skinPath'])) $responseTextures->textures->SKIN = (object) [ "url" => $user['skinPath'] ];
if (isset($user['capePath']) && !empty($user['capePath'])) $responseTextures->textures->CAPE = (object) [ "url" => $user['capePath'] ];
$response = (object) [
	'id' => str_replace('-', '', $user['clientToken']),
	'name' => $user['displayName'],
	'properties' => [(object)[
		'name' => 'textures',
		'value' => base64_encode(json_encode($responseTextures, JSON_UNESCAPED_SLASHES))
	]]
];
echo json_encode($response);
?>