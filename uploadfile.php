<!DOCTYPE html>
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>AQUATEC</title>
 <script src="js/bpif.js"></script>
</head>

<body style="" onload="doonload()">
<div class="w3-bar w3-dark-grey">
 <a href='index.php' class='w3-bar-item w3-button'>Home</a>
 <a id=signin href="login.php" class="w3-bar-item w3-button">SIGN IN</a>
 <div id=usermenu class="w3-dropdown-hover w3-right w3-dark-grey">
  <button id=username class="w3-button">X</button>
  <div class="w3-dropdown-content w3-bar-block w3-card-4">
   <a href="ulogout.php" class="w3-bar-item w3-button">LOGOUT</a>
  </div>
 </div>
</div>
<div class="w3-container w3-card-4 w3-light-grey w3-text-green w3-margin">
<?php
$success = false;
$cookied = @$_COOKIE['_u'];//
//$cookied = true;
if (!$cookied) {
 echo "<h1>You need to log on before you can use this program</h1>";
} elseif (empty($_FILES)) {
?>
<form action="uploadfile.php" method="post" enctype="multipart/form-data">
<h2 class="w3-center">File Upload</h2>
<div class="w3-row w3-section">
  <input class="w3-button w3-block w3-section w3-green w3-ripple w3-padding" type="file" name="filesrc" accept=".txt, .csv, .edi">
  <input type="submit" class="w3-button w3-block w3-section w3-green w3-ripple w3-padding" value="Click to Upload">
</div>
</form>
<ol>
 Instructions
 <li>Select the file you want to upload</li>
 <li>Click the File Upload bar.</li>
 <li>Wait for the file to upload. Check the bottom left of your browser screen to view progress</li>
</ul>
<?php
} else {
 foreach($_FILES as $fileid=>$fileitem) {
  if (@$_FILES["file$i"]["error"] == 1) {
   $response = "Error ".$_FILES[$fileid]["error"];
  } else {
   $fname = $fileitem['name'];
   $ext = strtolower(substr(strrchr($fname, '.'), 1));
   if ($ext == 'txt' || $ext == 'csv' || $ext == 'tdf') {
    $destination = strtoupper('uploads\\'.$fname);
    $action   = move_uploaded_file($fileitem['tmp_name'], $destination);
    if ($action) {$response = $fname;$success = true;
    } else {$response = 'Failed to move '.$destination;}
   } else {$response = 'Invalid file type '.$ext;}
  }
 }
 if ($success) {
  echo "<h2>File $fname has been uploaded</h2>";
 } else {
  echo "<h2>File failed to upload $fname</h2>";
  echo "<h2>$response</h2>";
 }
}

?>
</div>