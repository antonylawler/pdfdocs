<?php

$call  = @$_REQUEST['call'];
$resp  = json_decode($call);
$conn  = new mysqli("127.0.0.1","root","password","docs");
$stmt  = 'update apdocs set ';

$stmt .= "doctype = '".$conn->real_escape_string($resp[13])."'";
$stmt .= " ,posted = itemid "; 
if ($resp[13] != 'O') {
 $stmt .= ", supplierid = '".$conn->real_escape_string($resp[12])."'";
 if ($resp[13] != 'S') {
  $stmt .= " ,invoiceno = '".$conn->real_escape_string($resp[15])."'";
  $stmt .= " ,purchaseorder = '".$conn->real_escape_string($resp[16])."'";
  $stmt .= " ,taxdate = '".$conn->real_escape_string($resp[17])."'";
  $stmt .= " ,goods = '".$conn->real_escape_string($resp[18]/100)."'";
  $stmt .= " ,vat = '".$conn->real_escape_string($resp[19]/100)."'";
  $stmt .= " ,total = '".$conn->real_escape_string($resp[20]/100)."'";
  $stmt .= " ,otherjson = '".$conn->real_escape_string(json_encode($resp[23]))."'";
 }
}
$stmt .= " where itemid = $resp[0]";
$result = mysqli_query($conn,$stmt); 
mysqli_close($conn);
if ($resp[13] == 'I' || $resp[13] == 'C') {
 $sourcefile = $resp[6].' '.$resp[10].' '.$resp[11];
 $taxdate = explode('-',$resp[17]); $taxdate = substr($taxdate[0],2).$taxdate[1].$taxdate[2];
 $o  = "STX=ANA:1+CREATE:$resp[12]+1234567890123:[DESTNAME]+".date('ymd').":".date('His')."+99999+INVFIL'";

 $o .= "MHD=1+INVFIL:9'";
 $o .= "TYP=0700+INVOICES'";
 $o .= "SDT=$resp[12]:$resp[12]+[SUPPNAME]'";
 $o .= "CDT=+:[CUSTACCNO]'";
 $o .= "FIL=99999+1+".date('ymd')."'";
 $o .= "MTR=6'";

 $o .= "MHD=2+INVOIC:9'";
 $o .= "CLO=:+[DELADD1]:[DELADD2]:[DELADD3]:[DELADD4]'";
 $o .= "ODD=1+$resp[16]+[DELIVNOTE]'";
 $o .= "IRF=$resp[15]+$taxdate+$taxdate'";
 $o .= "ILD=1+1+:FROMPDF++:++1+".($resp[18]*100)."+".($resp[18]*100)."+S+[VATRATE]+++$sourcefile'";
 $o .= "STL=1+S+[VATRATE]+1+$resp[18]+++++$resp[18]++$resp[18]+$resp[19]+$resp[20]+$resp[20]'";
 $o .= "TLR=1+$resp[18]+++++$resp[18]++$resp[18]+$resp[19]+$resp[20]+$resp[20]'";
 $o .= "MTR=7'";

 $o .= "MHD=3+VATTLR:9'";
 $o .= "VRS=1+S+[VATRATE]+$resp[18]+$resp[18]+$resp[19]+$resp[20]+$resp[20]'";
 $o .= "MTR=3'";

 $o .= "MHD=4+INVTLR:9'";
 $o .= "TOT=$resp[18]+$resp[18]+$resp[19]+$resp[20]+$resp[20]+1'";
 $o .= "MTR=3'";

 $o .= "END=4";
 
 file_put_contents('PDF'.$resp[0].'.EDI',$o);
}
echo $stmt;

?>