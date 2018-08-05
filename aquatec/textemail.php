<?php
namespace MyProject;
require 'includes/phpmailer/src/PHPMailer.php';
require 'includes/phpmailer/src/SMTP.php';
require 'includes/phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

$dest     = @$_REQUEST['dest'];
$body     = @$_REQUEST['body'];
$subject  = @$_REQUEST['subject'];
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
	$mail->Subject    = $subject;
	$mail->From       = "tim@aquatecplumbingsupplies.co.uk";
	$mail->FromName   = "Invoices";
	$mail->AddAddress($dest);
	$mail->IsHTML(true);
	$mail->Body       = "<pre>$body</pre>";
	$mail->Host       = "mail.aquatecplumbingsupplies.co.uk";
	$mail->Send();
	echo "Message sent" ;
} catch (phpmailerException $e) {
	echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
	echo $e->getMessage(); //Boring error messages from anything else!
}
?>
