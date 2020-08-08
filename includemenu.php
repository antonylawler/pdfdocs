<!DOCTYPE html>
<?php
 require_once ("include.php");
 $userid = authenticate(0);
 if ($userid == '') $userid = 'ANON';
?>
<html>
<head>
 <link rel="stylesheet" href="css/w3.css">
 <title>EDITOR</title>
</head>

<body style="background:black;color:white;"">
<div class="w3-bar w3-green">
 <a class='w3-bar-item w3-button' href='index.php'>Menu</a>
 <a class='w3-bar-item w3-center' id=progname></a>
 <div id=usermenu class="w3-dropdown-hover w3-right w3-indigo">
  <button id=username class="w3-button"><?php echo $userid;?></button>
  <div class="w3-dropdown-content w3-bar-block w3-card-4">
   <a href="ulogout.php" class="w3-bar-item w3-button">LOGOUT</a>
  </div>
 </div>
</div>
