<?php
error_reporting(E_ALL);
$server = "{mail.aquatecplumbingsupplies.co.uk:143/imap/novalidate-cert/norsh/notls/readonly}Inbox" ;
$pwd = "bronte1234";
$user = 'invoices@aquatecplumbingsupplies.co.uk';
getallmail($server,$user,$pwd);

function getallmail($server,$user,$pwd) {

 global $counter;
$counter = 1;
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
 global $counter;
 $workdir = "testinvoices" ;

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
   $fname =  $workdir . 'IMG'.($counter++) . '.PDF';
   print($fname."\n");
   file_put_contents($fname,$data);
  }
 }
 if (isset($p->parts)) {
  foreach ($p->parts as $sub => $p) {
   echo "Hunting through sub-parts ".$h->subject."\n";
   getpart($mbox, $mid, $p, $partno . '.' . ($sub + 1),$h,$s);
  }
 }

}

?>