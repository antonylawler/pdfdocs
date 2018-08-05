<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>PLUMLINK</title>
 <script src="js/bpif.js"></script>
</head>

<body style="" onload="doonload()">
<script>
var thisprog = "EXPORT.PL.QUERIES";
</script>
<div class="w3-bar w3-dark-grey">
 <a href='menu.php' class='w3-bar-item w3-button'>Plumlink Home</a>
 <a id=signin href="login.php" class="w3-bar-item w3-button">SIGN IN</a>
 <div id=usermenu class="w3-dropdown-hover w3-right w3-dark-grey">
  <button id=username class="w3-button">X</button>
  <div class="w3-dropdown-content w3-bar-block w3-card-4">
   <a href="ulogout.php" class="w3-bar-item w3-button">LOGOUT</a>
  </div>
 </div>
</div>
<h1 id="thisprog"></h1>
<div id="middles" class="w3-responsive w3-border-all">
<div class='w3-panel w3-grey'><h3>File Downloaded as requested</h3></div>
</div>
</body>
</html>
<script>
function localdodefault() {
 callserver(thisprog+'\x14LIST\x14',fillmiddle);
}

function fillmiddle(val) {
 var pom = document.createElement('a');
 var csvContent=val[3];
 var blob = new Blob([csvContent],{type: 'text/csv;charset=utf-8;'});
 var url = URL.createObjectURL(blob);
 pom.href = url;
 pom.setAttribute('download', 'foo.csv');
 pom.click();
}
</script>
