<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** validate.php - /validate Endpoint
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('validate');
$fa->checkInput();
$input = $fa->getInput(true);

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($input) . "\n\n");
$accessToken = htmlspecialchars($input->accessToken);
if ($fa->getUserByAccessToken($accessToken)) { http_response_code(204); die(); }
else $fa->errOut(403);
?>