<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');

$request = exploderequest();
$firstline = 90;
$lastline = 180;
$ypos = 999999;
$pdf = new TCPDF();
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->setFooterMargin(0);
$pdf->setHeaderMargin(0);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));

$stat = $request['stat'];

$types = array('INV','CRN','CSH','DIS','JNL','JNL','JNL');

for ($i = 0 ; $i < count($stat[1]);$i++) {
 
	if ($ypos > $lastline) {
  $ypos == 999999 ?	firstheader() : otherheader();
		$ypos = $firstline;
	} else {
		$pdf->Line(15, $ypos , 195, $ypos);
	}

 $pdf->SetFont(null,'',10);

 $bits = explode('_',$stat[1][$i]);
 $pdf->Text(15,$ypos,depickdate($bits[1]));
 $pdf->Text(40,$ypos,$bits[3]);
 $pdf->Text(70,$ypos,$types[($bits[2]-1)/10]);
 $pdf->Text(90,$ypos,$bits[5]);

 list($w,$d) = numsizes($bits[4]);
 
 if ($d > 0) {
  $pdf->Text(148-$w,$ypos,$d);
 } else {
  $pdf->Text(168-$w,$ypos,$d);
 }

 list($w,$d) = numsizes($bits[6]);
 $pdf->Text(190-$w,$ypos,$d);

	$ypos = $ypos + 5 ;

}
lastfooter();

$filename = __DIR__.'/pdfdocs/STAT_'.$request['asatdate'][0].'_'.$request['id'][0].'.PDF';
$pdf->Output($filename, 'F');
echo $filename;
exit;

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
 global $pdf,$request;
 $stat = $request['stat'];
	$pdf->AddPage();
	$pdf->setJPEGQuality(600);
	$pdf->image('COLOGO.jpg',15,12,60);
 $pdf->SetAlpha(0.05);
 $pdf->image('Symbol.jpg',70,95,80);
 $pdf->setFont(null,'B',110);
 $pdf->setAlpha(1);
 $pdf->SetFont(null,'',10);
 $pdf->Text(20,40,$request['customer'][0]);
 $pdf->Text(20,44,$request['customer'][1]);
 $pdf->Text(20,48,$request['customer'][2]);
 $pdf->Text(20,52,$request['customer'][3]);
 $pdf->Text(20,56,$request['customer'][4]);
 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 $t = 'STATEMENT';
	$pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetAlpha(1);

 $pdf->SetFont(null,'',13);

 $t = 'Account Number : '.$request['id'][0] ;
	$pdf->Text(195-$pdf->getStringWidth($t),29,$t);

 $months = $request['months'];
 $period = $request['asatdate'][0];
 $t = 'Date : '.$months[substr($period,4,2)*1-1].' '.substr($period,0,4);
 
	$pdf->Text(195-$pdf->getStringWidth($t),36,$t);

 $pdf->SetFont(null,'',10); 

	$pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;

 $pdf->Text(175,78,"Page $thispage of $pages"); 

 $pdf->SetFont(null,'B',10);

	$pdf->Line(15,85,195,85);
 $pdf->Line(15,90,195,90);

 $pdf->Text(15,85,'Date');
 $pdf->Text(40,85,'Ref');
 $pdf->Text(70,85,'Type');
 $pdf->Text(90,85,'Your Ref');
 $pdf->Text(148,85,'Dr');
 $pdf->Text(168,85,'Cr');
 $pdf->Text(188,85,'Bal');

}

function lastfooter() {

 global $pdf,$request;

	$pdf->Line(15,250,195,250);
 $ages = ['Old','60 Days','30 Days','Curr Mth'];
 for ($i = 0;$i<4;$i++) {
  $t = $ages[3-$i];
  $pdf->Text(15,253+$i*5,$t);
  list($w,$d) = numsizes(@$request['agearray'][$i]);
  $pdf->Text(50-$w,253+$i*5,$d);
 }
 $pdf->SetFont(null,'N',10); 

 $pdf->Text(70,252,'Pay by cheque to: ');
 $pdf->Text(71,257,ucwords(strtolower($request['company'][0])));
 $pdf->Text(71,261,ucwords(strtolower($request['company'][1])));
 $pdf->Text(71,265,ucwords(strtolower($request['company'][2])));
 $pdf->Text(71,269,ucwords(strtolower($request['company'][3])));
 $pdf->Text(71,273,$request['company'][4]);


 $pdf->Text(110,252,'Pay By Direct Credit To');
/*
 $bank = explode(' ',$request['company'][9]);
 $pdf->Text(111,257,'Bank Sort : '.$bank[0]);
 $pdf->Text(111,261,'Bank Acc : '.$bank[1]);
 $pdf->Text(111,265,'Reference: '.$doc[0]);
*/

}

?>