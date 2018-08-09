<?php
//$server = "{mail.aquatecplumbingsupplies.co.uk:143/imap/novalidate-cert/norsh/notls/readonly}Inbox" ;
//getallmail($server,'invoices@aquatecplumbingsupplies.co.uk','bronte1234');
getemailtext();

function getallmail($server,$user,$pwd) {
 $mbox = imap_open($server,$user,$pwd);
 if ($mbox) $msglist = imap_search($mbox, 'SEEN');
 if ($msglist) {
  foreach ($msglist as $mid) {
   $h = imap_headerinfo($mbox, $mid);
   if (sizeof(gotmessage(@$h->message_id))>0) continue;
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

function gotmessage($mid) {
 $stmt = "select itemid from apdocs where emailuid = '$mid'";
 $dblink  = getconn();
 $results = [];
 $result  = mysqli_query($dblink,$stmt);
 if ($result) {while($row = mysqli_fetch_row($result)){$results[]=$row;}}
 mysqli_close($dblink); 
 return $results;

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
 $stmt   = "insert into apdocs (itemid,name,editversion,editdate,editby,EmailUID,filename,otherjson) values (0,'$name',1,sysdate(),'bot','$emailuid','$fname','{}')";
 $dblink = getconn();
 $result = mysqli_query($dblink,$stmt);
 mysqli_close($dblink);
}

function getunpaged() {
 $stmt    = "select * from apdocs";
 $dblink  = getconn();
 $results = [];
 $result  = mysqli_query($dblink,$stmt);
 if ($result) {while($row = mysqli_fetch_row($result)){$results[]=$row;}}
 mysqli_close($dblink); 
 return $results;
}

function getemailtext() {
 $list = getunpaged();
 foreach ($list as $listid=>$apdocsitem) {
  print("Processing $apdocsitem[0]\n");
  $conn = getconn();
  $stmt = $conn->prepare("update apdocs set pages=?, scanned=?, textfromfile=?,textinfo=? where itemid =?");
  $stmt->bind_param("sssss",$pages,$scanned,$textfromfile,$textinfo,$itemid);
  $fname    = $apdocsitem[6];
  $itemid   = $apdocsitem[0];
  $resp     = '';exec("qpdf --decrypt --show-npages $fname",$resp);
  $pages    = $resp[0];
  $text     = array();
  $textpos  = array();
  if ($pages == 1) {
   $pagefrom = 1;$pageto = 1;
  } elseif ($apdocsitem[11] != '') {
   $pagefrom = $apdocsitem[10];$pageto = $apdocsitem[11];
  } else {
   $pagefrom = 1;$pageto = $pages;
  }
  for ($p=$pagefrom;$p<=$pageto;$p++) {
   exec("qpdf --decrypt --linearize --empty --pages $fname $p -- WORK.PDF");
   $jpgname = explode('.',$fname); $jpgname = "images/".$jpgname[0]."-".$p;
   exec("pdftocairo -q -jpeg -singlefile -r 300 WORK.PDF $jpgname");

   $allwords = ''; exec("pdftotext -layout -nopgbrk WORK.PDF -",$allwords); $allwords = implode(' ',$allwords);
   preg_match_all("|[a-zA-Z]{3,100}|",$allwords,$ans);

   $scanned = '';
   if (sizeof($ans[0]) < 6) {
    exec("tesseract $jpgname.jpg -c tessedit_char_whitelist=\"@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$&*()-=+:;<>?/,.\" WORK pdf");
    $allwords = ''; exec("pdftotext -layout -nopgbrk WORK.PDF -",$allwords); $allwords = implode(' ',$allwords);
    $scanned = 'Y';
   }
   list($width,$height,$poswords) = respfromxml();

   $text[$p-1] = str_replace(array("'","\f"),'',$allwords);
   $textpos[$p-1] = [$poswords,$width,$height];
  }
  $textfromfile = implode("\f",preg_replace('/\s+/',' ',$text));
  $textinfo = json_encode($textpos);

  $stmt->execute();
 }

}
function respfromxml() {
 $ans = '';exec("pdftotext -bbox -nopgbrk WORK.PDF -" ,$ans);
 $ans = utf8_for_xml(implode("\n",$ans));
 $dom = new DOMDocument;
 $dom->loadXML($ans);
 if (!$dom) {echo "Could not parse";}
 $xml      = simplexml_import_dom($dom);
 $words    = $xml->body->doc->page;
 $width    = $words['width']*1;
 $height   = $words['height']*1;
 $allwords = [];$tposwords = [];$poswords = [];
 if (isset($words)) {
  foreach($words->children() as $word=>$w) {
   $v           = [$w['yMin']*1000+$w['xMin']*1,$w['xMin']*1,$w['yMin']*1,$w['xMax']*1,$w['yMax']*1,$w[0].""];
   $tposwords[] = $v;
  }
 }
 asort($tposwords);
 foreach ($tposwords as $vid=>$w) {
  $allwords[] = $w[5]."";
  $poswords[] = [$w[1],$w[2],$w[3],$w[4],trim($w[5])];
 }
 return array($width,$height,$poswords);
}
function utf8_for_xml($string) {return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);}
?>