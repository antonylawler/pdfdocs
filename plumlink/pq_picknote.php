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
		$pdf->Line(15, $ypos-2 , 195, $ypos-2);
	}
 $pdf->SetFont(null,'',10);
 $pdf->Text(15,$ypos,$doc[6][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(53-$w,$ypos, $num);
 $num = round($doc[10][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(64-$w,$ypos, $num);
 $num = round($doc[11][$i]);
 $w = $pdf->getStringWidth($num);
	$pdf->Text(75-$w,$ypos, $num);
 $pdf->Text(104,$ypos,ucwords(strtolower($doc[7][$i])));	
	$ypos = $ypos + 9 ;
}

lastfooter();
$filename = __DIR__.'/pdfdocs/PICKING_'.$request['id'][0].'.PDF';
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
 $pdf->SetAlpha(0.05);


 $pdf->SetAlpha(0.09);
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
 $t = 'PICKING NOTE';
	$pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetFont(null,'',13);
	$pdf->Text(145,29,'Doc. No.');
	$pdf->Text(145,36,'Tax Date');
	$pdf->Text(145,43,'Your reference');
 $pdf->SetAlpha(1);

 $t = $request['id'][0] ;
	$pdf->Text(195-$pdf->getStringWidth($t),29,$t);

 $t = depickdate($doc[1]) ;
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
 $pdf->Text(40,85,'Order Q');
 $pdf->Text(55,85,'Deliv');
 $pdf->Text(65,85,'T Foll');
 $pdf->Text(80,85,'Manuf Code');
 $pdf->Text(104,85,'Description');
 

}

function lastfooter() {
 global $pdf,$request;

}

?>
