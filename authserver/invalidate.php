<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** invalidate.php - /invalidate Endpoint
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('invalidate');
$fa->checkInput();
$input = $fa->getInput(true);

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($input) . "\n\n");
$clientToken = htmlspecialchars($input->clientToken);

if (!$user = $fa->getUserByClientToken($clientToken)) $fa->errOut(403);
if (!$input->accessToken || $input->clientToken != $user['accessToken']) $fa->errOut(403);

if ($fa->setUser($userName, ['accessToken'=>''])) { http_response_code(204); die(); }
else $fa->errOut(401);
?>