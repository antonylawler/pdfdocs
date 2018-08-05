<?php
function gettextt() {

 $directory = './';
 
 $files = array_diff(scandir($directory), array('..', '.'));
 foreach ($files as $id=>$fname) {
 $conn = new mysqli("127.0.0.1","root","password","docs");
 $stmt= $conn->prepare("insert into apdocs values(0,'',1,sysdate(),'Editby','EmailUid',?,?,?,?,0,0);");
 $stmt->bind_param("ssss",$pages,$scanned,$fname,$pdftext);
 print($fname);
  $ext = strtoupper(strrchr($fname,'.'));
  if ($ext == '.PDF') {
   exec("qpdf --decrypt --linearize $fname D$fname"); 
   $resp = ''; exec("pdfinfo D$fname",$resp);
   preg_match("/Pages:\s*[1-9][0-9]{0,2}/",implode(' ',$resp),$resp);
   preg_match("/[1-9][0-9]{0,2}/",$resp[0],$resp);
   $pages = $resp[0];
   $text = array();
   for ($p=1;$p<=$pages;$p++) {
    $resp = ''; exec ("pdftotext -layout -f $p -l $p -nopgbrk $fname - ",$resp);
    $resp = implode(" ",$resp);
    preg_match_all("|[a-zA-Z]{3,100}|",$resp,$ans);
    $scanned = '';
    if (sizeof($ans[0]) < 6) {
     $cmd = "pdftocairo -f $p -l $p  -jpeg -singlefile -r 300 $fname ocr";
     exec ($cmd);
     $cmd = "tesseract ocr.jpg stdout -c tessedit_char_whitelist=\"@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$&*()-=+:;<>?/,.\"";
     $ocrresp = ''; exec ($cmd,$ocrresp);
     $ocrresp = implode(" ",$ocrresp);
     preg_match_all("|[a-zA-Z]{5,100}|",$ocrresp,$ans);
     if (sizeof($ans[0]) > 10) {$resp = $ocrresp;$scanned = 'Y';}
    }
    $text[$p] = str_replace(array("'","\f"),'',$resp);
   }
   $pdftext = implode("\f",$text);
   $stmt->execute();
   print_r($stmt);
  }
 $conn = null;
 }
}

gettextt();

?>