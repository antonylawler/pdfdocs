<?php
namespace MyProject;
require 'includes/phpmailer/src/PHPMailer.php';
require 'includes/phpmailer/src/SMTP.php';
require 'includes/phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

/*
number@myfastsms.co.uk
Subject as who message appears to be from
username in first line of email
token is second line
message on the remainder
8c70-8dbb-d867-7f52
*/

$dest = $_REQUEST['dest']; //number@myfastsms.co.uk
$body = $_REQUEST['body'];
$dest = '07584709114';
$body = "Special Character test. Apostrophe ' Pound £ &amp; Not a smilie :) Dollar $";
$mail = new PHPMailer(true);
try {
	$mail->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true));
	$mail->SMTPDebug  = 0;  
	$mail->IsSMTP();       
	$mail->SMTPAuth   = true;
	$mail->SMTPSecure = "tls";
	$mail->Port       = 587;
	$mail->Host       = "smtp.gmail.com";
	$mail->Username   = "invoices@aquatecplumbingsupplies.co.uk";
	$mail->Password   = "bronte1234";
	$mail->Subject    = basename($filename.' From Aquatec Atached');
	$mail->From       = "info@plumlink.co.uk";
	$mail->FromName   = "Invoices";
    $b                = "FS29597\n8c70-8dbb-d867-7f52\nExample message\n";
	$mail->Body       = $b.$body;
	$mail->AddAddress($dest.'@my.fastsms.co.uk');
	$mail->IsHTML(false);
	$mail->Send();
	echo "Text sent" ;
} catch (phpmailerException $e) {
	echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
	echo $e->getMessage(); //Boring error messages from anything else!
}
?>
