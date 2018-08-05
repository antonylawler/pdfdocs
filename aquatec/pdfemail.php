<?php
namespace MyProject;
require 'includes/phpmailer/src/PHPMailer.php';
require 'includes/phpmailer/src/SMTP.php';
require 'includes/phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

$filename = $_REQUEST['filename'];
$dest     = $_REQUEST['dest'];
$body     = $_REQUEST['body'];
$mail     = new PHPMailer(true);
try {
	$mail->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true));
	$mail->SMTPDebug  = 0;  
	$mail->IsSMTP();       
	$mail->SMTPAuth   = true;
	$mail->SMTPSecure = "tls";
	$mail->Port       = 25;
	$mail->Username   = "invoices@aquatecplumbingsupplies.co.uk";
	$mail->Password   = "bronte1234";
	$mail->Subject    = basename($filename.' From Aquatec Atached');
	$mail->From       = "invoices@aquatecplumbingsupplies.co.uk";
	$mail->FromName   = "Invoices";
	$mail->AddAddress($dest);
	$mail->AddAttachment($filename);
	$mail->IsHTML(true);
	//$mail->AddEmbeddedImage('FOOTERCOLOGO.jpg','cologo','Company Logo');
	$add = '';
	$mail->Body = $body.'<table><tr><td><img src="cid:cologo"></td><td style="font-family:arial">'.$add.'</td></tr></table>';
	$mail->Host       = "mail.aquatecplumbingsupplies.co.uk";
	$mail->Send();
	echo "Message sent" ;
} catch (phpmailerException $e) {
	echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
	echo $e->getMessage(); //Boring error messages from anything else!
}
?>
