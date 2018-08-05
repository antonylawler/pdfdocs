<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');
$d = @$_REQUEST['suppliers'];
$suppliers = array();
$d = explode(chr(254),$d);
foreach ($d as $id=>$item) {
 $item = explode(chr(253),$item);
	$suppliers[$item[0]] = $item ;
}

$docs = array();
$d = @$_REQUEST['docs'];
$d = explode(chr(254),$d);
foreach ($d as $id=>$ledgeritem) {
 $ledgeritem = explode(chr(253),$ledgeritem);
 $ledgerid = $ledgeritem[0];
 $docs[$ledgerid] = $ledgeritem;
}

$matchlist = array();
$d = @$_REQUEST['matchlist'];
$d = explode(chr(254),$d);
foreach ($d as $matchid=>$matchitem) {
 $matchitem = explode(chr(253),$matchitem);
 $matchlist[$matchitem[0]] = $matchitem;
}
$paydate = date('d/m/Y');

$request = exploderequest();

$pdf = new TCPDF();
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->setFooterMargin(0);
$pdf->setHeaderMargin(0);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));

foreach ($matchlist as $matchid=>$matchitem) {
 remittance($matchid);
}
file_put_contents('remitt.txt',print_r($matchlist,1));
file_put_contents('requestx.txt',print_r($docs,1));

$filename = __DIR__ .'/pdfdocs/REMITTANCE_'.$request['id'][0].'.PDF';
echo $filename;
$pdf->Output($filename, 'F');

exit;

function remittance($matchid) {
 global $suppliers,$docs,$pdf,$request,$matchlist,$supplierid;
 $firstline = 90;
 $lastline = 250;
 $ypos = 999999;
 $total = 0;

 $ledgerids = explode(chr(252),$matchlist[$matchid][1]);
 $ledgervals = explode(chr(252),$matchlist[$matchid][2]);
 
 foreach ($ledgerids as $i=>$ledgerid) {

  if ($ledgerid != $matchid) {
  
  $ledgeritem = $docs[$ledgerid];
  $supplierid = $ledgeritem[1];
	 if ($ypos > $lastline) {
   $ypos == 999999 ?	firstheader() : otherheader();
		 $ypos = $firstline;
	 } else {
		 $pdf->Line(15, $ypos , 195, $ypos);
 	}

  $pdf->SetFont(null,'',10);
  $pdf->Text(15,$ypos,depickdate($ledgeritem[2]));
  $pdf->Text(30,$ypos,$ledgeritem[0]);
  $pdf->Text(65,$ypos,$ledgeritem[3]);
  list($w,$d) = numsizes($ledgeritem[5]);
  $pdf->Text(130-$w,$ypos,$d);
  list($w,$d) = numsizes($ledgeritem[8]);
  $pdf->Text(162-$w,$ypos,$d);
  list($w,$d) = numsizes($ledgervals[$i]);
  $pdf->Text(190-$w,$ypos,$d);

  $total += $ledgeritem[5];
 	$ypos = $ypos + 5 ;
  }
 }
 lastfooter();
}


function otherheader() {
 otherfooter();
 firstheader();
}

function otherfooter() {
 global $pdf;
	$pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;
 $pdf->Text(160,275,"Continued on next page."); 
}

function firstheader() {
 global $pdf,$request,$suppliers,$supplierid,$paydate,$docs,$matchid;
 $doc = $request['doc'];
	$pdf->AddPage();
	$pdf->setJPEGQuality(600);
	$pdf->image('COLOGO.jpg',15,12,60);
	$pdf->SetAlpha(0.05);
 $pdf->image('Symbol.jpg',70,95,80);
 $supplier = $suppliers[$supplierid];

 
 $pdf->setAlpha(1);
 $pdf->SetFont(null,'',10);
 $pdf->Text(20,40,$supplier[1]);
 $pdf->Text(20,44,$supplier[2]);
 $pdf->Text(20,48,$supplier[3]);
 $pdf->Text(20,52,$supplier[4]);
 $pdf->Text(20,56,$supplier[5]);
 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 $t = 'REMITTANCE ADVICE';

	$pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetFont(null,'',13);
	$pdf->Text(145,29,'Batch Ref.');
	$pdf->Text(145,36,'Date');
 $pdf->SetAlpha(1);

 $t = $request['id'][0].'/'.$supplierid ;
	$pdf->Text(195-$pdf->getStringWidth($t),29,$t);

 $t = $paydate;
	$pdf->Text(195-$pdf->getStringWidth($t),36,$t);
//	$pdf->Text(195-$pdf->getStringWidth($t),36,time());

 $pdf->SetFont(null,'',10); 

	$pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;

// $pdf->Text(175,78,"Page $thispage of $pages"); 

 $pdf->SetFont(null,'B',10);

	$pdf->Line(15,85,195,85);
 $pdf->Line(15,90,195,90);

 $pdf->Text(15,85,'Date');
 $pdf->Text(30,85,'Our Ref');
 $pdf->Text(65,85,'Your Ref');
 $pdf->Text(125,85,'Value');
 $pdf->Text(155,85,'Disc %');
 $pdf->Text(185,85,'Paid');

}

function lastfooter() {
 global $pdf,$request,$matchid,$docs;

	$pdf->Line(15,250,195,250);
 $doc = $request['doc'];

 $pdf->SetFont(null,'',10);

 $pdf->Text(70,252,'');
 $pdf->Text(15,257,ucwords(strtolower($request['company'][0])));
 $pdf->Text(15,261,ucwords(strtolower($request['company'][1])));
 $pdf->Text(15,265,ucwords(strtolower($request['company'][2])));
 $pdf->Text(15,269,ucwords(strtolower($request['company'][3])));
 $pdf->Text(15,273,$request['company'][4]);

 list($w,$d) = numsizes($docs[$matchid][5]*-1);
 
 $pdf->Text(150,252,'Payment Value ');
 $pdf->Text(190-$w,252,$d);

}

?>
