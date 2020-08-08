<?php
 require_once ("include.php");
 
 if ($_REQUEST['userid']) {
  $userid = authenticate();
  if($userid == '') {
   header("Location: failedlogin.php");
  } else {
   header("Location:index.php");
  }
  exit;
 }
?>
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>LOGIN</title>
</head>
<body style='background:black'>
<p style='padding:10px'></p>
<div class='w3-row'>
<div class="w3-col m4"><p></p></div>
<div class="w3-col m4">
<div class='w3-container w3-green'><h2>Login</h2></div>
<div class="w3-container w3-card-4 w3-light-grey w3-cell-middle">
<form>
<label>User</label>
<input class="w3-input" type="text" placeholder="Enter Username" name="userid" autofocus value='' required>
<label>Password</label>
<input class="w3-input" type="password" placeholder="Enter Password" name="password" value='' required>
<label>Remember me</label>
<input class="w3-check w3-margin-top" type="checkbox" checked="checked" name="cookie">
<button class='w3-green w3-button'>Submit</button>
</form>
</div>
</div>
<div class="w3-col m4"></div>
</div>
</body>
</html>
​