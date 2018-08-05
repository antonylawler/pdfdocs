<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<link rel="stylesheet" href="css/w3.css">
<head>
 <title>PLUMLINK</title>
 <script src="js/bpif.js"></script>
 <script>
  thisprog = 'login';
  function logonuser(response) {
   if (response[0] != 'NOTLOGGEDON') {
    storeuser(response);
    window.location = 'menu.php';
   } else {
    window.location = 'failedlogin.php';
   }
  }
 </script>
</head>
<body style='background:black'>
<p style='padding:100px'></p>
<div class='w3-row w3-animate-top'>
<div class="w3-col m4"><p></p></div>
<div class="w3-col m4">
<div class='w3-container w3-green'><h2>Failed Logon Attempt</h2></div>
<div class="w3-container w3-card-4 w3-light-grey w3-cell-middle">
<label>Your login attempt has failed.</label>
<label>Note that your IP has been logged and repeated failures will result in your source being blacklisted</label>
<a href=login.php>Click to retry</a>
</div>
</div>
<div class="w3-col m4"></div>
</div>
</body>
</html>
​