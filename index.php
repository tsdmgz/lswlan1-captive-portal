<?php

/////////////////////////////////////////////////////////////////////
// EDIT THESE SETTINGS
//
// General Settings
$server_name = "cwdwr";
$domain_name = "wardroom";
$site_name = "HMS COLLINGWOOD Wardroom Network";
$arp = "/usr/sbin/arp";
//
// These addresses will only be shown syops - for Internet Suite PCs
$internetpc[0] = "00:19:d1:02:1b:02";
$internetpc[1] = "00:19:d1:01:de:73";
$internetpc[2] = "00:19:d1:02:1c:76";
$internetpc[3] = "00:19:d1:02:19:10";

//
// Array for drop down box to select block
$blocks[0] = 'Roope';
$blocks[1] = 'Sherlock';
$blocks[2] = 'Old accommodation';
$blocks[4] = 'Other';

//
// Simplelists email list details
$simplelists_list = 'collingwood@simplelists.com';

$email_list_message = "A Wardroom email list is available to subscribe to.
This is used to provide you with<br>details about mess functions
during your time at COLLINGWOOD, and is especially useful<br>if you
are on course with no access to the COLLINGWOOD network.
If you would like to<br>receive these occasional notices please enter your
email address below.<br><br>Your email address will be kept private and you
can remove yourself from the list at<br>any time with instructions contained
in each email.<br><br>";
$email_list_message2 = "<h2>This is not a spam service! You will receive
occasional, useful emails about the Wardroom and socials.</h2>";
////////////////////////////////////////////////////////////////////


// The following file is used to keep track of users
global $users;
$users = "/var/lib/users";

// Check if we've been redirected by firewall to here. If so redirect to registration address
if ($_SERVER['SERVER_NAME']!="$server_name.$domain_name") {
  header("location:http://$server_name.$domain_name/index.php?add=".urlencode($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']));
  exit;
}

# AUP
$aup = file_get_contents('aup.txt');


// Get blocked users
$blocked = array();
$current_users = file($users);
foreach ($current_users as $v) {
        $v = trim($v);
	$fields = split("\t",$v,7);
	if (strpos($fields[4],"*")) {
            $blocked[] = trim($fields[4],'*');
        }
}

// Attempt to get the client's mac address
global $mac;
$mac = shell_exec("$arp -a ".$_SERVER['REMOTE_ADDR']);
preg_match('/..:..:..:..:..:../',$mac , $matches);
@$mac = $matches[0];
if (!isset($mac)) { exit; }

session_start();

// First check if we're looking at an internet PC
// If so just show syops
if (in_array($mac,$internetpc)) {

  if (!isset($_POST['agree'])) {
  
    print_header();

    echo "
      <h1>Welcome to $site_name</h1>
      You must agree to the Acceptable Use Policy before using these machines:<br><br>

      <form method='post'>

      <table border=0 cellpadding=5 cellspacing=0>
      <tr><td><textarea name='aup' readonly rows='20' cols='80'>$aup</textarea></td></tr>
      <tr><td align=center height='23'><input type='submit' name='agree' value='I agree to the Acceptable Use Policy'></td></tr>
      </table>
      </form>";
  }  else {
    enable_address();
  }
  exit;
}

// Check if they are blocked for some reason
// If so show them a message and exit
if (in_array($mac,$blocked)) {

    print_header();

    echo "
      <h1>Your PC has been blocked from the internet</h1>
      This could be for a number of reasons, but most probably because you have
      entered incorrect information when you signed up, or you have a virus.<br>
      Please get in touch with the IT rep.<br>";
    exit;
}

// If the email box has been submitted then we are at the final step.
// Log the email if need be and enable the user on the network
if (isset($_POST['email'])) {

  if ($_POST['email']) {
    $name = urlencode($_SESSION['name']);
    $email = urlencode($_POST['email']);
    $list = urlencode($simplelists_list);
    fopen("http://www.simplelists.com/subscribe.php?name=$name&email=$email&action=subscribe&list=$list","r");
    enable_address();
  } else {
    error_log("Doesn't want the email list: ".$_SESSION['name'].",".$_SESSION['cabin']);
    enable_address();
  }
}

print_header();

// See if the user has got as far as accepting syops
// If so, show them details of email list
if (isset($_POST['agree'])) {
  echo "<h1>$site_name email list</h1>";
  echo $email_list_message;  
  echo "<form method='POST'>
    <table border=0 cellpadding=5 cellspacing=0>
    <tr><td width='150' align='right'>Email address:</td><td><input size='40' type='text' name='email'></td></tr>
    <tr><td></td><td><input type='submit' name='emailnext' value='Next >>'></td></tr>
    </table>
    </form>";
  echo $email_list_message2;
    
  print_footer();
  exit;
}

// See if any of the first page has been completed
if (isset($_POST['name'])) {
	$name = trim($_POST['name']);
	if (!eregi('^[[:print:]]+$', trim($_POST['cabin']))) {
		$name="";
	}
	if (!eregi('^[[:print:]]+$', trim($_POST['wing']))) {
		$name="";
	}
} else {
	$name = "";
}

// By now, $name will only be set if they have *fully* completed the first page
// If it's not then show them the first page
if (!eregi('^[[:print:]]+$', $name)) {
  ?>

  <h1>Welcome to <?php echo $site_name;?></h1>
  To access the Internet you must first enter your details:<br><br>
  <form method='POST'>
  <table border=0 cellpadding=5 cellspacing=0>
  <tr><td>Your full name:</td><td><input type='text' name='name'></td></tr>
  <tr><td>Cabin number:</td><td><input type='text' name='cabin'></td></tr>
  <tr><td>Block:</td><td><select name='wing'>
  <?php
      foreach ($blocks as $v) {
          echo "<option value='$v'>$v";
      }
  ?>
  </select></td></tr>
  <tr><td></td><td><input type='submit' name='next' value='Next >>'></td></tr>
  </table>
  </form>
  <br>
  <b>Note: This system and all connected computers are for UNCLASSIFIED material only<b>
  <?php
} else {
  // If they have fully completed the first page then log the details and ask them to agree to AUP
  ?>

  <h1>Welcome to <?php echo $site_name;?></h1>
  You must also agree to the Acceptable Use Policy:<br><br>

  <form method='post'>
  
  <table border=0 cellpadding=5 cellspacing=0>
  <tr><td><textarea name='aup' readonly rows='10' cols='80'><?php echo $aup; ?></textarea></td></tr>
  <tr><td align=center height='23'><input type='submit' name='agree' value='I agree to the Acceptable Use Policy'></td></tr>
  </table>
  </form>
  <?php

  $_SESSION['name'] = $name;
  $_SESSION['cabin'] = $_POST['cabin'];
  $_SESSION['wing'] = $_POST['wing'];
}

print_footer();

// This function enables the PC on the system by calling iptables, and also saving the
// details in the users file for next time the firewall is reset

function enable_address() {

    global $users;
    global $mac;
    global $internetpc;

    if (!in_array($mac,$internetpc)) {
      file_put_contents($users,$_SESSION['name']."\t".$_SESSION['wing']."\t".$_SESSION['cabin']."\t".$_SERVER['REMOTE_ADDR']."\t$mac\t".date("d.m.Y")."\n",FILE_APPEND + LOCK_EX);
    }
    
    // Add PC to the firewall
    exec("sudo iptables -I internet 1 -t nat -m mac --mac-source $mac -j RETURN");
    // The following line removes connection tracking for the PC
    // This clears any previous (incorrect) route info for the redirection
    exec("sudo rmtrack ".$_SERVER['REMOTE_ADDR']);

    sleep(1);
    header("location:http://".$_GET['add']);
    exit;
}

// Function to print page header
function print_header() {

  ?>
  <html>
  <head><title>Welcome to <?php echo $site_name;?></title>
  <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  <LINK rel="stylesheet" type="text/css" href="./style.css">
  </head>

  <body bgcolor=#FFFFFF text=000000>
  <?php
}

// Function to print page footer
function print_footer() {
  echo "</body>";
  echo "</html>";

}

?>
