<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>BRANDPARTNERS</title>
 <script src="js/bpif.js"></script>
 <script src="js/datechooser.js"></script>
</head>
<style>
.dateChooser td {cursor:default;text-align:center;font-family:arial;font-size:11px;color:black; }
.dateChooser td.dateChooserActive:hover {background:black;color:white;}
.dateChooser td.dateChooserActiveToday {border: 2px solid red;}
.dateChooser th {font-family:arial;font-size:11px;background: black; color: white;width: 19px;border: none;}
.dateChooser option, .dateChooser select {font-size:10px;}
.dateChooser {border: 1px outset black; background:white; padding: 1px;}
.dateChooser table {text-align:right;border:2px solid green;background:black;}
.currency {text-align:right;}
i {background:red;}
</style>
<body style="background:black;color:white;" onload="doonload()">
<script>
var thisprog = "<?php echo($_REQUEST['THISPROG'])?>";
function localdodefault() {
 if (sessionStorage.u) callserver(thisprog+'\x14LIST',showresponse);
}
</script>
<div class="w3-bar w3-green">
 <a href='menu.php' class='w3-bar-item w3-button'>BRANDPARTNERS HOME</a>
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
</html>