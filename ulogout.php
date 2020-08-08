<?php
 session_start();
 unset($_SESSION["user"]);
 unset($_SESSION["groups"]);
 unset($_SESSION["schema"]);
 setcookie("_u","");
?>
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>Logout</title>
</head>
<body style='background:black'>
<p style='padding:100px'></p>
<div class='w3-row'>
 <div class="w3-col m4"><p></p></div>
 <div class="w3-col m4">
 <div class='w3-container w3-green'><h2>You have Been Logged Out</h2></div>
 <div class="w3-container w3-card-4 w3-light-grey w3-cell-middle">
  <label>Where do you want to go now ?</label>
  <a class='w3-button w3-block w3-green' href='login.php'>Log Back In</a>
  <p></p>
  <a class='w3-button w3-block w3-green' href='newindex.php'>Home Page</a>
  <p></p>
 </div>
</div>
<div class="w3-col m4"></div>
</body>
</html>
​