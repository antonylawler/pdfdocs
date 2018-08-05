<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../includes/phpmailer/class.phpmailer.php');

$filename = $_REQUEST['filename'];
$dest     = $_REQUEST['dest'];
$body     = $_REQUEST['body'];
$mail = new PHPMailer();             
$mail->SMTPDebug  = 0;  
$mail->IsSMTP();       
$mail->SMTPAuth   = true;
$mail->SMTPSecure = "tls";
$mail->Port       = 587;
$mail->Username   = "admin@plumlink.co.uk";
$mail->Password   = "plumlink123";

$mail->Subject    = basename($filename.' From Plumlink Atached');
$mail->From       = "admin@plumlink.co.uk";

$mail->FromName   = "Plumlink";
$mail->AddAddress($dest);
$mail->AddAttachment($filename);
$mail->IsHTML(true);
//$mail->AddEmbeddedImage('plumlink\\FOOTERCOLOGO.jpg','cologo','Company Logo');
$add = '';

$mail->Body = $body.'<table><tr><td><img src="cid:cologo"></td><td style="font-family:arial">'.$add.'</td></tr></table>';
$mail->Host       = "smtp.gmail.com";

if($mail->Send()) {echo "Message sent" ;} else {echo "Message not sent" ;};
 file_put_contents('request.txt',print_r($_REQUEST,1));
?>
