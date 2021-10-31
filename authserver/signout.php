<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** signout.php - /signout Endpoint
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('signout');
$fa->checkInput();
$input = $fa->getInput(true);

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($input) . "\n\n");
$userName = htmlspecialchars($input->username);

if (!$user = $fa->getUserByName($userName)) $fa->errOut(403);
$inputPass = $fa->hash(htmlspecialchars($input->password));
if (!hash_equals($user['password'], $inputPass)) $fa->errOut(401);
if ($fa->setUser($userName, ['accessToken'=>''])) { http_response_code(204); die(); }
else $fa->errOut(401);
?>