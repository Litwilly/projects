<?php
/*****************************************************
  sendMail.php
   Sends Email using sendMail() fuction
   Pulls fields from db to send as subject and body
******************************************************/

function sendMail()
{
	// Open a MySQL connection
	include("db_connect_test.php");
	
	// Get last row of db
	$sql = "SELECT * FROM bp ORDER BY idp DESC LIMIT 1";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	// Assign title of last entry to variable
	$latest_title = $row[2];
	// Assign url of last entry to variable
	$latest_url = $row[1];
	
	// Include the Mail package
	require "Mail.php";
	
	// Identify the sender, recipient, mail subject, and body
	$sender    = "[Enter Sender]";
	$recipient = "[Enter Recipient]";
	$subject   = $latest_title;
	$body      = $latest_url;

	// Identify the mail server, username, password, and port
	include("email_connect.php");

	// Set up the mail headers
	$headers = array(
			"From"    => $sender,
			"To"      => $recipient,
			"Subject" => $subject
	);

	// Configure the mailer mechanism
	$smtp = Mail::factory("smtp",
			array(
					"host"     => $server,
					"username" => $username,
					"password" => $password,
					"auth"     => true,
					"port"     => 465
			)
	);

	// Send the message
	$mail = $smtp->send($recipient, $headers, $body);

	if (PEAR::isError($mail)) {
		echo ($mail->getMessage());
	}
}

?>