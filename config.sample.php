<?php
function getConfig() {
	return [
		'db' => [
			'host' => '',
			'name' => '',
			'user' => '',
			'pass' => ''
		],
		'loggingPrefix' => [
			'authenticate' => 'AU_',
			'invalidate' => 'IN_',
			'refresh' => 'RE_',
			'signout' => 'SO_',
			'validate' => 'VA_',
			'profile' => 'PF_',
			'join' => 'JN_',
			'hasJoined' => 'HJ_'
		]
	];
}
