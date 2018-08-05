<?php
$server = "{mail.aquatecplumbingsupplies.co.uk:143/imap/novalidate-cert/norsh/notls/readonly}Inbox" ;
//getallmail($server,'invoices@aquatecplumbingsupplies.co.uk','bronte1234');
//getemailtext();
sortpagesplit() ;
function sortpagesplit() {
 $cmd = "python pagesplit.py";
 exec ($cmd,$ans);
}

function getallmail($server,$user,$pwd) {
 $mbox = imap_open($server,$user,$pwd);
 if ($mbox) $msglist = imap_search($mbox, 'SEEN');
 if ($msglist) {
  foreach ($msglist as $mid) {
   $h = imap_headerinfo($mbox, $mid);
   $s = imap_fetchstructure($mbox, $mid);
   if (property_exists($s,'parts')) {
    foreach ($s->parts as $partno => $p) getpart($mbox, $mid, $p, $partno + 1,$h,$s);
   } else {
    echo "No attachments";
   }
  }
 } // Check Msglist
 imap_close($mbox); 
}

function getpart($mbox, $mid, $p, $partno,$h,$s) {
 if (isset($p->subtype) && isset($p->bytes) && $p->bytes > 2000) {
  $sourcetype = '';  
  isset($p->dparameters) ? $params = $p->dparameters : $params = $p->parameters;
  foreach ($params as $dpid=>$dpitem) {
   if ($dpitem->attribute == 'name' || $dpitem->attribute == 'filename') $sourcetype = strtolower(pathinfo($dpitem->value, PATHINFO_EXTENSION)) ;
  }
  if ($sourcetype == 'pdf') {
   $data = imap_fetchbody($mbox, $mid, $partno);
   if ($p->encoding == 4) {
    $data = quoted_printable_decode($data);
   } elseif ($p->encoding == 3) {
    $data = base64_decode($data);
   }
   $fname = getcounter('PDF','1').'.PDF' ;
   file_put_contents($fname,$data);
   dbinsert(mb_decode_mimeheader(@$h->subject),@$h->message_id,$fname);
  }
 }
 if (isset($p->parts)) {
  foreach ($p->parts as $sub => $p) {
   echo "Hunting through sub-parts ".$h->subject."\n";
   getpart($mbox, $mid, $p, $partno . '.' . ($sub + 1),$h,$s);
  }
 }
}


function getcounter($type,$section) {
 $dblink = getconn();
 $comm   = "call uniqueid('".$type."','".$section."')";
 $result = mysqli_query($dblink,$comm);
 $row    = mysqli_fetch_array($result);
 mysqli_close($dblink);
 return $row[0];
}

function getconn() {
 $conn = new mysqli("127.0.0.1","root","password","docs");
 return $conn;
}

function dbinsert($name,$emailuid,$fname) {
 $stmt = "insert into apdocs (itemid,name,editversion,editdate,editby,EmailUID,filename) values (0,'$name',1,sysdate(),'bot','$emailuid','$fname')";
 $dblink = getconn();
 $result = mysqli_query($dblink,$stmt);
 mysqli_close($dblink);
}

function getunpaged() {
 $stmt = "select * from apdocs where pages is null";
 $dblink = getconn();
 $results = [];
 $result = mysqli_query($dblink,$stmt);
 if ($result) {while($row = mysqli_fetch_row($result)){$results[]=$row;}}
 mysqli_close($dblink); 
 return $results;
}

function getemailtext() {
 $list = getunpaged();
 foreach ($list as $listid=>$apdocsitem) {
  $conn = getconn();
  $stmt= $conn->prepare("update apdocs set pages=?, scanned=?, textfromfile=? where itemid = ?");
  $stmt->bind_param("ssss",$pages,$scanned,$text,$apdocsitem[0]);

  exec("qpdf --decrypt --linearize $apdocsitem[6] WORK.PDF");
  $resp = ''; exec("pdfinfo WORK.PDF",$resp);
  preg_match("/Pages:\s*[1-9][0-9]{0,2}/",implode(' ',$resp),$resp);
  preg_match("/[1-9][0-9]{0,2}/",$resp[0],$resp);
  $pages = $resp[0];
  $text = array();

  for ($p=1;$p<=$pages;$p++) {
   $resp = ''; exec ("pdftotext -layout -f $p -l $p -nopgbrk WORK.PDF - ",$resp);
   $resp = implode(" ",$resp);
   preg_match_all("|[a-zA-Z]{3,100}|",$resp,$ans);
   $scanned = '';
   if (sizeof($ans[0]) < 6) {
    $cmd = "pdftocairo -f $p -l $p  -jpeg -singlefile -r 300 WORK.PDF ocr";
    exec ($cmd);
    $cmd = "tesseract ocr.jpg stdout -c tessedit_char_whitelist=\"@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$&*()-=+:;<>?/,.\"";
    $ocrresp = ''; exec ($cmd,$ocrresp);
    $ocrresp = implode(" ",$ocrresp);
    preg_match_all("|[a-zA-Z]{5,100}|",$ocrresp,$ans);
    if (sizeof($ans[0]) > 10) {$resp = $ocrresp;$scanned = 'Y';}
    print($scanned);
   }
   $text[$p] = str_replace(array("'","\f"),'',$resp);
  }
  $text = implode("\f",$text);
  $stmt->execute();
  mysqli_close($conn);
 }
 $conn = getconn();
 $result = mysqli_query($conn,'update apdocs set pagefrom=1,pageto=1 where pages=1');
 mysqli_close($conn);

}


?>