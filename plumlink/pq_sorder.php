<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');
$request = exploderequest();

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
 $ypos = $ypos + 2;
 $pdf->SetFont(null,'',10);
 $pdf->Text(15,$ypos,$doc[6][$i]);
 $num = round($doc[8][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(69-$w,$ypos, $num);
 $num = round($doc[10][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(81-$w,$ypos, $num);
 $num = round($doc[11][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(93-$w,$ypos, $num);
 $pdf->Text(95,$ypos,ucwords(strtolower($doc[7][$i])));
	$ypos = $ypos + 6 ;
}
lastfooter();

$filename = __DIR__.'/pdfdocs/SORDER_'.$request['id'][0].'.PDF';
echo $filename;
$pdf->Output($filename, 'F');

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
 
 $pdf->setAlpha(1);
 $pdf->SetFont(null,'',10);
 $pdf->Text(20,40,$request['customer'][0]);
 $pdf->Text(20,44,$request['customer'][1]);
 $pdf->Text(20,48,$request['customer'][2]);
 $pdf->Text(20,52,$request['customer'][3]);
 $pdf->Text(20,56,$request['customer'][4]);
 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 $t = $request['ordertype'][0];

	$pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetFont(null,'',13);
	$pdf->Text(145,29,'Pick No.');
	$pdf->Text(145,36,'Required');
	$pdf->Text(145,43,'Your reference');
 $pdf->SetAlpha(1);

 $t = $request['id'][0] ;
	$pdf->Text(195-$pdf->getStringWidth($t),29,$t);

 $t = depickdate($doc[5]) ;
	$pdf->Text(195-$pdf->getStringWidth($t),36,$t);

 $pdf->SetFont(null,'',10); 
 $pdf->Text(145,50,$doc[34]);

	$pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;

 $pdf->Text(175,78,"Page $thispage of $pages"); 

 $pdf->SetFont(null,'B',10);

	$pdf->Line(15,85,195,85);
 $pdf->Line(15,90,195,90);
 $pdf->Text(15,85,'Product Code');
 $pdf->Text(43,85,'Picked');
 $pdf->Text(58,85,'Order');
 $pdf->Text(70,85,'Deliv');
 $pdf->Text(82,85,'ToFoll');
 $pdf->Text(95,85,'Description');


}

function lastfooter() {
 global $pdf,$request;

	$pdf->Line(15,250,195,250);
 $doc = $request['doc'];

 $pdf->SetFont(null,'',10);
 $pdf->Text(15,252,'Delivery Address');
 $pdf->Text(16,257,ucwords(strtolower(substr($doc[33][0],0,31))));
 $pdf->Text(16,261,ucwords(strtolower(substr($doc[33][1],0,31))));
 $pdf->Text(16,265,ucwords(strtolower(substr($doc[33][2],0,31))));
 $pdf->Text(16,269,ucwords(strtolower(substr($doc[33][3],0,31))));
 $pdf->Text(16,273,ucwords(strtolower(substr($doc[33][4],0,31))));


 $pdf->Text(71,257,ucwords(strtolower($request['company'][0])));
 $pdf->Text(71,261,ucwords(strtolower($request['company'][1])));
 $pdf->Text(71,265,ucwords(strtolower($request['company'][2])));
 $pdf->Text(71,269,ucwords(strtolower($request['company'][3])));
 $pdf->Text(71,273,$request['company'][4]);

}

?>
