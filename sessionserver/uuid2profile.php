<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** uuid2profile.php - Get profile information
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('profile');

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($_GET) . "\n\n");

if (!$user = $fa->getUserByUUID($_GET['uuid'])) $fa->errOut[403];
if (!isset($user['clientToken'])) $fa->errOut[403];

$responseTextures = (object) [
	'timestamp' => (floor(microtime(true) * 1000)),
	'profileId' => str_replace('-', '', $user['clientToken']),
	'profileName' => $user['displayName'],
	'isPublic' => true,
	'textures' => (object) [],
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

fwrite($logHandle, "Result:\n" . json_encode($response) . "\n\n");
// This errs the launcher, but still prevents prevents it from freezing when it doesn't get a response
echo json_encode($response);
?>