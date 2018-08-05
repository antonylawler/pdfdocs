<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<link rel="stylesheet" href="css/w3.css">
<head>
 <title>BRANDPARTNERS</title>
 <script src="js/bpif.js"></script>
 <script>
  thisprog = 'login';
  function logonuser(response) {
   if (response[0] != 'NOTLOGGEDON') {
    storeuser(response);
    window.location = 'index.php';
   } else {
    window.location = 'failedlogin.php';
   }
  }
 </script>
</head>
<body style='background:black'>
<p style='padding:100px'></p>
<div class='w3-row'>
<div class="w3-col m4"><p></p></div>
<div class="w3-col m4">
<div class='w3-container w3-green'><h2>Login</h2></div>
<div class="w3-container w3-card-4 w3-light-grey w3-cell-middle">

<label>Username</label>
<input class="w3-input" type="text" placeholder="Enter Username" name="usrname" autofocus id=f_0 value='x' required>
<label>Password</label>
<input class="w3-input" type="password" placeholder="Enter Password" name="psw" value='x' id=f_1 required>
<label>Remember me</label>
<input class="w3-check w3-margin-top" type="checkbox" checked="checked" id=f_2>
<button type="button" onclick='sendinputs(logonuser)' class="w3-button w3-green">Submit</button>

</div>
</div>
<div class="w3-col m4"></div>
</div>
</body>
</html>
​