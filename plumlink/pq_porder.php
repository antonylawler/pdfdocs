<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');
$request = exploderequest();
file_put_contents('request.txt',print_r($request,1));
$firstline = 90;
$lastline = 250;
$ypos = 999999;
$pdf = new TCPDF();
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->setFooterMargin(0);
$pdf->setHeaderMargin(0);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));

$doc = $request['doc'];

for ($i = 0 ; $i < count($doc[6]);$i++) {
	if ($ypos > $lastline) {
  $ypos == 999999 ?	firstheader() : otherheader();
		$ypos = $firstline;
	} else {
		$pdf->Line(15, $ypos , 195, $ypos);
	}

 $pdf->SetFont(null,'',10);

 $num = round($doc[8][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(28-$w,$ypos, $num);

 $num = round($doc[11][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(43-$w,$ypos, $num);

 $pdf->Text(45,$ypos,$doc[6][$i]);

 $pdf->Text(73,$ypos,$doc[12][$i]);

 $desc = substr($doc[7][$i],0,55);
 $pdf->Text(100,$ypos,$desc);

 $num = round($doc[11][$i])/10000;
 list($w,$d) = numsizes($doc[9][$i]/100);
 $pdf->Text(190-$w,$ypos,$d);

	$ypos = $ypos + 5 ;
}
lastfooter();

$filename = __DIR__ .'/pdfdocs/PORDER_'.$request['id'][0].'.PDF';
echo $filename;
$pdf->Output($filename, 'F');
//file_put_contents('endrequest.txt',serialize($_REQUEST['doc']));

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
 $doc = $request['doc'];
	$pdf->AddPage();
	$pdf->setJPEGQuality(600);
	$pdf->image('COLOGO.jpg',15,12,60);
	 $pdf->SetAlpha(0.05);
 $pdf->image('Symbol.jpg',70,95,80);

 
 $pdf->setAlpha(1);
 $pdf->SetFont(null,'',10);
 $pdf->Text(20,40,$request['supplier'][0]);
 $pdf->Text(20,44,$request['supplier'][1]);
 $pdf->Text(20,48,$request['supplier'][2]);
 $pdf->Text(20,52,$request['supplier'][3]);
 $pdf->Text(20,56,$request['supplier'][4]);
 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 $t = 'PURCHASE ORDER';

	$pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetFont(null,'',13);
	$pdf->Text(145,29,'Order No.');
	$pdf->Text(145,36,'Required');
 $pdf->SetAlpha(1);

 $t = $request['id'][0] ;
	$pdf->Text(195-$pdf->getStringWidth($t),29,$t);

 $t = depickdate($doc[18][0]) ;
	$pdf->Text(195-$pdf->getStringWidth($t),36,$t);

 $pdf->SetFont(null,'',10); 
// $pdf->Text(145,50,$doc[34]);

	$pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;

 $pdf->Text(175,78,"Page $thispage of $pages"); 

 $pdf->SetFont(null,'B',10);

	$pdf->Line(15,85,195,85);
 $pdf->Line(15,90,195,90);
 $pdf->Text(14,85,'Ordered');
 $pdf->Text(30,85,'TFollow');
 $pdf->Text(45,85,'Our Code');
 $pdf->Text(73,85,'Supp Code');
 $pdf->Text(100,85,'Description');
 $pdf->Text(185,85,'Value');

}

function lastfooter() {
 global $pdf,$request;

	$pdf->Line(15,250,195,250);
 $doc = $request['doc'];

 $pdf->SetFont(null,'',10);
 $pdf->Text(15,252,'Delivery Address');
 $pdf->Text(16,257,ucwords(strtolower(substr($doc[16][0],0,31))));
 $pdf->Text(16,261,ucwords(strtolower(substr($doc[16][1],0,31))));
 $pdf->Text(16,265,ucwords(strtolower(substr($doc[16][2],0,31))));
 $pdf->Text(16,269,ucwords(strtolower(substr($doc[16][3],0,31))));
 $pdf->Text(16,273,ucwords(strtolower(substr($doc[16][4],0,31))));

 $pdf->Text(70,252,'Invoice Address');
 $pdf->Text(71,257,ucwords(strtolower($request['company'][0])));
 $pdf->Text(71,261,ucwords(strtolower($request['company'][1])));
 $pdf->Text(71,265,ucwords(strtolower($request['company'][2])));
 $pdf->Text(71,269,ucwords(strtolower($request['company'][3])));
 $pdf->Text(71,273,$request['company'][4]);

 list($w,$d) = numsizes(array_sum($request['doc'][9])/100);
 
 $pdf->Text(150,252,'Order Value  ');
 $pdf->Text(190-$w,252,$d);

 $pdf->Text(15,287,'Note that the above price is the price we are expecting to pay. If it is incorrect then please do not supply');
}

?>
