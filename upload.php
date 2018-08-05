<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<script>

function dochange() {
 var files ;
 event.dataTransfer ? files = event.dataTransfer.files : files = event.target.files;
 var fd = new FormData();

 for (var i=0;i<files.length;i++) fd.append('file'+i, files[i], files[i].name);
 var xhr = new XMLHttpRequest();
 xhr.open('POST','ajaxuploadtext.php',true);
 xhr.onload = function() {
  if (xhr.status === 200) {
   var j = JSON.parse(xhr.response);
   if (j[0]) {
    document.getElementById('confirm').innerHTML = '<h3>File Upload Complete</h3><h3>'+j[1]+'</h3>';
   } else {
    document.getElementById('confirm').innerHTML = '<h3>File Upload Failed</h3><h3>'+j[1]+'</h3>';
   }
  }
 }
 xhr.send(fd);
}

</script>

<?php
$c = @$_COOKIE['_u'];
if ($c == '') {
 modal();
} else {
 showit();
}

function modal() {
print("<body style='background:black'>");
print("<p style='padding:100px'></p>");
print("<div class='w3-row'>");
print("<div class='w3-col m4'><p></p></div>");
print("<div class='w3-col m4'>");
print("<div class='w3-container w3-green'><h2>Login to use this service</h2></div>");
print("<div class='w3-container w3-green'><a class='w3-button w3-grey' href='login.php'>Login</a></div>");
print("<div class='w3-container w3-card-4 w3-light-grey w3-cell-middle'>");
print("</div>");
print("</div>");
print("<div class='w3-col m4'></div>");
print("</div>");
print("</body>");
}

function showit() {
print("<div class='w3-container w3-blue'><h2>Upload File</h2></div>");
print("<form>");
print("<input class='w3-input w3-border w3-light-grey' type=file onchange=dochange() accept='.csv,*.txt'>");
print("</form>");
print("<div id='confirm' class='w3-green'>");
print("</div>");
print("<div class='w3-container w3-blue'>");
print("<h2>Download File</h2>");
print("</div>");
print("<div class='w3-green'>");

$dir    = './';
$f = scandir($dir);
foreach ($f as $fid=>$fitem) {
 if ($fitem == '.' || $fitem == '..' || strpos($fitem,'/')) {
 } else {
  print("<a style='width:20%;text-align:left;' class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href=$fitem>$fitem</a>");
 }
}

}

?>
</div>