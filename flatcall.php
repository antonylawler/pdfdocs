<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/local.css">
<head>
 <title>HOME</title>
 <script src="js/bpif.js"></script>
 <script src="js/datechooser.js"></script>
</head>
<body style="background:black;color:white;" onload="doonload()">
<script>
var thisprog = "<?php echo($_REQUEST['THISPROG'])?>";
</script>
<div class="w3-bar w3-green">
 <a href='menu.php' class='w3-bar-item w3-button'>HOME</a>
 <a id=signin href="login.php" class="w3-bar-item w3-button">SIGN IN</a>
 <div id=usermenu class="w3-dropdown-hover w3-right w3-indigo">
  <button id=username class="w3-button">X</button>
  <div class="w3-dropdown-content w3-bar-block w3-card-4">
   <a href="ulogout.php" class="w3-bar-item w3-button">LOGOUT</a>
  </div>
 </div>
</div>
<h1 id="thisprog"></h1>
<div id="middles">
</div>
</body>
<script>
</script>
</html>