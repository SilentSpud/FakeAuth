<?php
require_once '../FakeAuth.php';
$fa = new FakeAuth('index');


if (isset($_GET['newuser'])) {
	if (isset($_POST['u_name'])) {
		$uName = htmlspecialchars($_POST['u_name']);
		$uPass = htmlspecialchars($_POST['u_pass']);
		$dName = htmlspecialchars($_POST['u_disp']);
		if (!preg_match("/[a-zA-Z0-9]+/", $dName)) die('Error: invalid display name.\nDisplay name can only contain alphanumeric characters.');
		$fa->newUser($uName, $uPass, $dName);
		Header('Location: index.php');
	} else { ?>

<html><head>
<title>FakeAuth Dashboard</title>
<style type="text/css">
body { font-family: sans-serif; }
table.users { width: 50%; border-collapse: collapse; }
table.users th { padding: 15px; }
table.users td { padding: 0; }
table.users td a { display: block; padding: 15px; text-decoration: none; color: #333; }
table.users td a:hover { background-color: #EDEDED; }
table.users td, th { font-size: 14pt; }
table.users td { text-align: center; }
table.users td:not(:last-child), th:not(:last-child) { border-right: 1px solid #000; }
table.users tr:not(:last-child) td, th { border-bottom: 1px solid #000; }
</style>
<script type="application/javascript">

</script>
</head>
<body>
<form method="POST" action=".?newuser">
<table><tbody>
<tr>
	<td>Username:</td>
	<td><input type="text" name="u_name" required="" pattern="[a-zA-Z0-9]+"></input></td>
</tr>
<tr>
	<td>Password:</td>
	<td><input type="password" name="u_pass" required=""></input></td>
</tr>
<tr>
	<td>Display Name:</td>
	<td><input type="text" name="u_disp" required="" pattern="[a-zA-Z0-9]+"></input></td>
</tr>
</tbody></table>
<input type="submit" value="Submit"></input>
</form>
</body></html>
<?php }
exit;
}
if (isset($_POST['action']) && $_POST['action'] == 'updateUser') {
	if (!$user = $fa->getUserByName(htmlspecialchars($_POST['u']))) die('Invalid User');
	
	// Verify given password hash
	$inputPass = $fa->hash(htmlspecialchars($_POST['p']));
	if (!hash_equals($user['password'], $inputPass)) die('Error: invalid pass');
	
	// Update passwords if necessary
	if (isset($_POST['np']) && !empty($_POST['np'])) {
		$newPass = $fa->hash(htmlspecialchars($_POST['np']));
		if (!hash_equals($fa->hash(htmlspecialchars($_POST['cp'])), $newPass)) die('Error: new passwords do not match');
		else $fa->setUser($user['username'], ['password' => $newPass]);
	}
	
	// Update desired name if necessary
	$dName = htmlspecialchars($_POST['d']);
	if (!preg_match("/[a-zA-Z0-9]+/", $dName)) die('Error: invalid display name. Display name can only contain alphanumeric characters.');
	if ($user['displayName'] != $dName) $fa->setUser($user['username'], ['displayName' => $dName]);
	
	if (isset($_FILES['skin']) && $_FILES['skin']['error'] == UPLOAD_ERR_OK) {
		$check = getimagesize($_FILES['skin']['tmp_name']);
		if ($check !== false) {
			$destPath = '../sessionserver/skins/' . $user['username'] . '.png';
			$destUrl = 'https://sessionserver.mojang.com/skins/' . $user['username'] . '.png';
			if (file_exists($destPath)) unlink($destPath);
			move_uploaded_file($_FILES['skin']['tmp_name'], $destPath);
			if ($user['skinPath'] != $destPath) $fa->setUser($user['username'], ['skinPath' => $destUrl]);
		}
	}
	
	if (isset($_FILES['cape']) && $_FILES['cape']['error'] == UPLOAD_ERR_OK) {
		$check = getimagesize($_FILES['cape']['tmp_name']);
		if ($check !== false) {
			$destPath = '../sessionserver/capes/' . $user['username'] . '.png';
			$destUrl = 'https://sessionserver.mojang.com/capes/' . $user['username'] . '.png';
			if (file_exists($destPath)) unlink($destPath);
			move_uploaded_file($_FILES['cape']['tmp_name'], $destPath);
			if ($user['capePath'] != $destPath) $fa->setUser($user['username'], ['capePath' => $destUrl]);
		}
	}
	echo 'User information updated.';
	exit;
}
?>

<html><head>
<title>FakeAuth Dashboard</title>
<style type="text/css">
body { font-family: sans-serif; }
table.users { width: 50%; border-collapse: collapse; }
table.users th { padding: 15px; }
table.users td { padding: 0; }
table.users td a { display: block; padding: 15px; text-decoration: none; color: #333; }
table.users td a:hover { background-color: #EDEDED; }
table.users td, th { font-size: 14pt; }
table.users td { text-align: center; }
table.users td:not(:last-child), th:not(:last-child) { border-right: 1px solid #000; }
table.users tr:not(:last-child) td, th { border-bottom: 1px solid #000; }
</style>
<script type="application/javascript">

</script>
</head>
<body>
<?php
if (isset($_GET['user']) && strlen($_GET['user']) > 0) {
	if (!$user = $fa->getUserByName(htmlspecialchars($_GET['user']))) die('Invalid User');
?>
<form method="POST" action="index.php" enctype="multipart/form-data">
<input type="hidden" name="action" value="updateUser"/>
<table><tbody>
<tr>
	<td>Username:</td>
	<td><input type="text" name="u" readonly="" value="<?php echo $user['username']; ?>"></input></td>
</tr>
<tr>
	<td>Old Password:</td>
	<td><input type="password" name="p" required=""></input></td>
</tr>
<tr>
	<td>New Password:</td>
	<td><input type="password" name="np"></input></td>
</tr>
<tr>
	<td>Confirm Password:</td>
	<td><input type="password" name="cp"></input></td>
</tr>
<tr>
	<td>Display Name:</td>
	<td><input type="text" name="d" value="<?php echo $user['displayName']; ?>" pattern="[a-zA-Z0-9]+"></input></td>
</tr>
<tr>
	<td>Skin Path:</td>
	<td><input type="file" name="skin" accept="image/png"></input></td>
</tr>
<tr>
	<td>Cape Path:</td>
	<td><input type="file" name="cape" accept="image/png"></input></td>
</tr>
</tbody></table>
<input type="submit" value="Submit"></input>
</form>
</body></html>
<?php } else { ?>
<h1>Users:</h1>
<table class="users"><thead>
<tr>
<th>Username</th>
<th>Display Name</th>
<th>Token?</th>
</tr>
</thead>
<tbody>
<?php
if (!$users = $fa->getAllUsers()) die("Query failed");
for ($i = 0; $i < count($users); $i++) {
	$user = $users[$i];
	$hasToken = ((isset($user['clientToken']) && !empty($user['clientToken'])) ? 'Yes' : 'No');
	echo "<tr>\n";
	echo "\t<td><a href=\"?user=" . $user['username'] . "\">" . $user['username'] . "</a></td>\n";
	echo "\t<td><a href=\"?user=" . $user['username'] . "\">" . $user['displayName'] . "</a></td>\n";
	echo "\t<td><a href=\"?user=" . $user['username'] . "\">" . $hasToken . "</a></td>\n";
	echo "</tr>\n";
}
?>
</tbody></table>
<p><a href=".?newuser=1">Create New User</a></p>
</body><html>
<?php } ?>