<?php
session_start();
?>

<html>
<head><title>Wardroom internet admin</title>
<LINK rel="stylesheet" type="text/css" href="./style.css">
</head>
      
<body bgcolor=#FFFFFF text=000000>
          

<?php



if (isset($_POST['password'])) {
	if ($_POST['password'] == "jpaiscrap") {
		$_SESSION['password'] = "set";
	}
}

if (!isset($_SESSION['password'])) {
	echo "Please enter password:";
	echo "<form method='post'><input type='password' name='password'>
		<input type='submit' value='Logon'></form>";
	echo "</body></html>";
	exit;
}

// validate any inputs
if (isset($_GET['mac'])) {
	if (preg_match('/^..:..:..:..:..:..\*?$/',$_GET['mac'])) {
		$mac = $_GET['mac'];
	} else {
		echo "<font color='red'>Invalid MAC address</font><br><br>";
	}
}

$userfile = "/var/lib/users";

if (isset($_GET['action']) && $mac) {
	$mac = trim($mac,'*');
	if ($_GET['action']=="block") {
		$users = file_get_contents($userfile);
		if (!strpos($users,"$mac*")) {
			$users = str_replace($mac,$mac."*",$users);
			file_put_contents($userfile,$users,LOCK_EX);
		}
		exec("sudo iptables -D internet -t nat -m mac --mac-source $mac -j RETURN");
	} elseif ($_GET['action']=="unblock") {
		$users = file_get_contents($userfile);
		if (strpos($users,"$mac*")) {
			$users = str_replace($mac."*",$mac,$users);
			file_put_contents($userfile,$users,LOCK_EX);
			exec("sudo iptables -I internet 1 -t nat -m mac --mac-source $mac -j RETURN");
		}
	} elseif ($_GET['action']=="delete") {
		$users = file_get_contents($userfile);
		$users = preg_replace("/\n.+$mac.*\n/","\n",$users);
		file_put_contents($userfile,$users,LOCK_EX);
		exec("sudo iptables -D internet -t nat -m mac --mac-source $mac -j RETURN");
	} else {
		echo "<font color='red'>Invalid action requested</font><br><br>";
	}
}

$users = file("$userfile");

echo "<center><h1>Wardroom Internet Users</h1>";
echo "<a href='".$_SERVER['PHP_SELF']."'>Refresh page</center></a>";
echo "<h2>Notes:</h2>";
echo "* This is the <b>probable</b> IP address. It is possible that since signing up the
	user has been allocated a different IP address. To check the correct IP address
	of a MAC address use the 'arp' command.<br><br>";

echo "Deleting an entry will only force a user to re-sign back onto the system.<br><br>";
echo "Manual editing of these entries can be done by changing /var/lib/users<br><br>";
echo "<h2>Users</h2>";
echo "<table border='1'>";
echo "<tr>
	<td></td>
	<td><b></b></td>
	<td><b>Name</b></td>
	<td><b>Accom</b></td>
	<td><b>Cabin</b></td>
	<td><b>IP address*</b></td>
	<td><b>MAC address</b></td>
	<td><b>Start</b></td>
	<!-- <td><b>Comments</b></td> -->
	<td></td>
	</tr>";

$count = 0;
$users = array_reverse($users);
$self = $_SERVER['PHP_SELF'];

foreach ($users as $v) {
	$v = trim($v);
	$fields = split("\t",$v,7);
	if (!isset($fields[5])) { $fields[5] = "&nbsp;"; }
	echo "<tr>";
	echo "<td><a href='$self?action=delete&mac=".$fields[4]."'>Delete</a>\n";
	if (strpos($fields[4],"*")) {
		$fields[4] = trim($fields[4],'*');
		echo "<td><a href='$self?action=unblock&mac=".$fields[4]."'>Unblock</a>\n";
		$blocked = "red";
	} else {
		echo "<td><a href='$self?action=block&mac=".$fields[4]."'>Block</a>\n";
		$blocked = "black";
	}
	for ($i=0; $i<=5; $i++) {
		echo "<td><font color='$blocked'>".$fields[$i]."</font></td>\n";
	}
	echo "</tr>";
	$count++;
}

echo "</table></body></html>";

?>
