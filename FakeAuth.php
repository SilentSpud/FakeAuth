<?php
/***
 *** FakeAuth - Minecraft Yggrasil Authentication Server
 *** 
 *** FakeAuth.php - Global settings and functions
***/

class FakeAuth {
	public $dbConn;
	private $rootDir;
	private $configFile;
	private $errCodes = [
		400 => '{"error":"Unsupported Media Type","errorMessage":"The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method"}',
		401 => '{"error":"ForbiddenOperationException","errorMessage":"Invalid credentials. Invalid username or password."}',
		403 => '{"error":"IllegalArgumentException","errorMessage":"credentials is null"}',
		404 => '{"error":"Not Found","errorMessage":"The server has not found anything matching the request URI"}',
		405 => '{"error":"Method Not Allowed","errorMessage":"The method specified in the request is not allowed for the resource identified by the request URI"}',
		'4002' => '{"error":"IllegalArgumentException","errorMessage":"Invalid timestamp."}'
	];
	public $endpoint;
	public $config;
	public $log;
	public $users;
	
	function __construct (string $ep = 'general') {
		$this->endpoint = $ep;
		$this->rootDir = dirname(__FILE__);
		$this->configFile = '../config.php';
		require_once($this->configFile);
		$rawConfig = getConfig();
		if ($ep == 'general') $this->log = 'FA_';
		else if ($ep == 'index') $this->log = 'WB_';
		else $this->log = $rawConfig['loggingPrefix'][$this->endpoint];
		
		$dbInfo = $rawConfig['db'] or die('Failed to load configuration');
		$this->dbConn = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['pass'], $dbInfo['name']);
		if ($this->dbConn->connect_errno) {
			print "Error: Failed to make a MySQL connection: \n";
			print 'Errno: ' . $this->dbConn->connect_errno . "\n";
			print 'Error: ' . $this->dbConn->connect_error . "\n";
			die('MySQL Connection Error');
		}
	}
	
	function __destruct () {
		$this->dbConn->close();
	}
	
	public function genUUID ($dashes = true) {
		if ($dashes) return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,
			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
		else return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,
			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	public function errOut (int $errCode = 404) {
		if (!isset($this->errCodes[$errCode])) $errCode = 404;
		http_response_code($errCode);
		echo $this->errCodes[$errCode];
		exit;
	}
	
	public function checkInput (string $method = 'POST', bool $checkType = true) {
		if ($_SERVER['REQUEST_METHOD'] != $method) $this->errOut(405);
		if ($checkType && $_SERVER['CONTENT_TYPE'] != 'application/json') $this->errOut(400);
	}
	
	public function getInput ($checking = false) {
		$sh = fopen('php://input', 'r');
		$rawInput = stream_get_contents($sh);
		$input = json_decode($rawInput);
		fclose($sh);
		if (!$input) {
			if ($checking) $this->errOut(403);
			else return 403;
		}
		return $input;
	}
	public function hash (string $password) {
		return hash("sha384", $password);
	}
	
	public function getUserByClientToken (string $tokenVal) {
		$tokenVal = $this->dbConn->real_escape_string(htmlspecialchars(strip_tags($tokenVal)));
		$sql = "SELECT * FROM `users` WHERE `clientToken` = '$tokenVal'";
		if (!$result = $this->dbConn->query($sql)) {
			// Get the error information
			print "Error: Query failed to execute: \n";
			print "Query: " . $sql . "\n";
			print "Errno: " . $this->dbConn->errno . "\n";
			print "Error: " . $this->dbConn->error . "\n";
			exit;
		}
		if ($result->num_rows === 0) return false;
		return $result->fetch_assoc();
	}
	
	public function getUserByAccessToken (string $tokenVal) {
		$tokenVal = $this->dbConn->real_escape_string(htmlspecialchars(strip_tags($tokenVal)));
		$sql = "SELECT * FROM `users` WHERE `accessToken` = '$tokenVal'";
		if (!$result = $this->dbConn->query($sql)) {
			// Get the error information
			print "Error: Query failed to execute: \n";
			print "Query: " . $sql . "\n";
			print "Errno: " . $this->dbConn->errno . "\n";
			print "Error: " . $this->dbConn->error . "\n";
			exit;
		}
		if ($result->num_rows === 0) return false;
		return $result->fetch_assoc();
	}
	
	public function getUserByUUID (string $uuidVal) {
		$uuidVal = $this->dbConn->real_escape_string(htmlspecialchars(strip_tags($uuidVal)));
		$sql = "SELECT * FROM `users` WHERE REPLACE(clientToken, \"-\", \"\") = '$uuidVal'";
		if (!$result = $this->dbConn->query($sql)) {
			// Get the error information
			print "Error: Query failed to execute: \n";
			print "Query: " . $sql . "\n";
			print "Errno: " . $this->dbConn->errno . "\n";
			print "Error: " . $this->dbConn->error . "\n";
			exit;
		}
		if ($result->num_rows === 0) return false;
		return $result->fetch_assoc();
	}
	
	public function getUserByDisplayName (string $dNameVal) {
		$dNameVal = $this->dbConn->real_escape_string(htmlspecialchars(strip_tags($dNameVal)));
		$sql = "SELECT * FROM `users` WHERE `displayName` = '$dNameVal'";
		if (!$result = $this->dbConn->query($sql)) {
			// Get the error information
			print "Error: Query failed to execute: \n";
			print "Query: " . $sql . "\n";
			print "Errno: " . $this->dbConn->errno . "\n";
			print "Error: " . $this->dbConn->error . "\n";
			exit;
		}
		if ($result->num_rows === 0) return false;
		return $result->fetch_assoc();
	}
	
	public function getUserByName (string $name) {
		$name = $this->dbConn->real_escape_string(htmlspecialchars(strip_tags($name)));
		$sql = "SELECT * FROM `users` WHERE `username` = '$name'";
		if (!$result = $this->dbConn->query($sql)) {
			// Get the error information
			print "Error: Query failed to execute: \n";
			print "Query: " . $sql . "\n";
			print "Errno: " . $this->dbConn->errno . "\n";
			print "Error: " . $this->dbConn->error . "\n";
			exit;
		}
		if ($result->num_rows === 0) return false;
		return $result->fetch_assoc();
	}
	
	public function getAllUsers () {
		if (!$result = $this->dbConn->query("SELECT * FROM `users`")) {
			// Get the error information
			print "Error: Query failed to execute: \n";
			print 'Query: ' . $sql . "\n";
			print 'Errno: ' . $this->dbConn->errno . "\n";
			print 'Error: ' . $this->dbConn->error . "\n";
			exit;
		}
		if ($result->num_rows === 0) return false;
		$allUsers = [];
		while ($user = mysqli_fetch_assoc($result)) $allUsers[] = $user;
		return $allUsers;
	}
	
	public function setUser (string $name, $userInfo) {
		$sql = "UPDATE `users` SET ";
		foreach ($userInfo as $key => $val) {
			if (!in_array($key, ['password', 'displayName', 'clientToken', 'accessToken', 'skinPath', 'capePath'])) continue;
			$sql .= "`$key` = '$val', ";
		}
		$sql = substr($sql, 0, -2);
		$sql .= " WHERE `username` = '$name'";
		return $this->dbConn->query($sql) ? TRUE : $conn->error;
	}
	
	public function newUser (string $name, string $pass, string $dispName) {
		$pass = $this->hash($pass);
		$clientToken = $this->GenUUID();
		$sql = "INSERT INTO `users` (`username`,`password`,`clientToken`,`displayName`) VALUES ('$name','$pass','$clientToken','$dispName')";
		return $this->dbConn->query($sql) ? TRUE : $this->dbConn->error;
	}
}
?>