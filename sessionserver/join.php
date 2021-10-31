<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** join.php -session/minecraft/join
***/

require_once '../FakeAuth.php';
$fa = new FakeAuth('join');
$input = $fa->getInput(false);

$tmpfname = tempnam('logs/', $fa->log);
$logHandle = fopen($tmpfname, "w");
fwrite($logHandle, "Input:\n" . json_encode($input) . "\n\n");
if (!$user = $fa->getUserByUUID($input->selectedProfile)) $fa->errOut(403);
else if ($user['accessToken'] != $input->accessToken) $fa->errOut(403);
else http_response_code(204);
?>