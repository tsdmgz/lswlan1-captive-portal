<?php

// The following file is used to keep track of users
global $users;
$users = "/var/lib/users";
$arp = "/usr/sbin/arp";

global $mac;
$mac = shell_exec("$arp -a ".$_SERVER['REMOTE_ADDR']);
preg_match('/..:..:..:..:..:../',$mac , $matches);
$mac = $matches[0];


// Remove from users file so that not enabled again in future
$handle = fopen($users, "r+");
$newusers = "";
if (flock($handle,LOCK_EX)) {
	while(!feof($handle)) {
		$line = fgets($handle);
		if (!strpos($line,$mac)) { $newusers .= $line; }
	}
	ftruncate($handle,0);
	rewind($handle);
	fwrite($handle, $newusers);
	fclose($handle);
}


// Remove PC from the firewall
exec("sudo iptables -D internet -t nat -m mac --mac-source $mac -j RETURN");
// The following line removes connection tracking for the PC
// This clears any previous (incorrect) route info for the redirection
exec("sudo rmtrack ".$_SERVER['REMOTE_ADDR']);

sleep(1);

echo "Internet access to your PC has been disabled.<br>";

exit;


?>
