<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');

$request = exploderequest();
$pdf = new TCPDF();
$copy = 'CUSTOMER';
pdforder();
$copy = 'BRANCH';
pdforder();
$filename = __DIR__.'/pdfdocs/DELNOTE_'.$request['id'][0].'.PDF';
echo $filename;
$pdf->Output($filename, 'F');

function pdforder() {
 global $pdf,$request,$copy;
 $firstline = 90;
 $lastline = 240;
 $ypos = 999999;
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
  $pdf->Text(15,$ypos,$doc[6][$i]);
  $pdf->Text(60,$ypos,ucwords(strtolower($doc[7][$i])));
  $num = round($doc[10][$i]);
  $w = $pdf->getStringWidth($num);
 	$pdf->Text(58-$w,$ypos, $num);
 	$ypos = $ypos + 5 ;
 }
 lastfooter();

}

function otherheader() {
 otherfooter();
 firstheader();
}

function otherfooter() {
 lastfooter();
}

function firstheader() {
 global $pdf,$request;
 $doc = $request['doc'];
	$pdf->AddPage();
	$pdf->setJPEGQuality(600);
	$pdf->image('COLOGO.jpg',15,12,60);
 $pdf->SetAlpha(0.05);
 $pdf->image('Symbol.jpg',70,95,80);

 $pdf->SetAlpha(0.09);
 $pdf->setFont(null,'B',110);
 $pdf->setAlpha(1);
 $pdf->SetFont(null,'',10);

 $pdf->Text(20,40,ucwords(strtolower(substr($doc[33][0],0,45))));
 $pdf->Text(20,44,ucwords(strtolower(substr($doc[33][1],0,45))));
 $pdf->Text(20,48,ucwords(strtolower(substr($doc[33][2],0,45))));
 $pdf->Text(20,52,ucwords(strtolower(substr($doc[33][3],0,45))));
 $pdf->Text(20,56,ucwords(strtolower(substr($doc[33][4],0,45))));
 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 
 $t = 'DELIVERY NOTE';
 
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
 $pdf->Text(43,85,'Quantity');
 $pdf->Text(60,85,'Description');

}

function lastfooter() {
 global $pdf,$request,$copy;

	$pdf->Line(15,250,195,250);
 $doc = $request['doc'];

 $pdf->SetFont(null,'',10);
 $pdf->Text(50,252,'Customer');
 $pdf->Text(51,257,ucwords(strtolower($request['customer'][0])));
 $pdf->Text(51,261,ucwords(strtolower($request['customer'][1])));
 $pdf->Text(51,265,ucwords(strtolower($request['customer'][2])));
 $pdf->Text(51,269,ucwords(strtolower($request['customer'][3])));
 $pdf->Text(51,273,ucwords(strtolower($request['customer'][4])));

 
 $pdf->Text(15,287,'VAT Number:'.$request['company'][8].' e:admin@plumlink.co.uk '.' phone:'.$request['branch'][7]);

 $pdf->SetAlpha(.5);
 $pdf->SetFont(null,'',13);

 $pdf->Rect(150,250,45,30);

 $pdf->SetAlpha(.2);
 $pdf->SetFont(null,'B',10);
 $pdf->Text(152,251,$copy." Copy");
 $pdf->Text(152,256,'Sign Here');

 $pdf->SetAlpha(1);

 $style = array('border'=>2,'vpadding'=>'auto','hpadding'=>'auto','fgcolor'=>array(0,0,0),'bgcolor'=>false,'module_width'=>1,'module_height'=>1);
 $pdf->write2DBarcode('DN'.$request['id'][0] , 'QRCODE,L', 15, 250, 30, 30, $style, 'N');

 $pdf->SetFont(null,'N',10); 

 $pdf->Text(15,283,ucwords(strtolower($request['company'][0].','.$request['company'][1].','.$request['company'][2].','.$request['company'][3].',')).$request['company'][4]);


}

?>
