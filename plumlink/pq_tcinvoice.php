<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');

$request = exploderequest();
$pdf = new TCPDF();
$copy = 'CUSTOMER';
pdfinvoice();
$copy = 'BRANCH';
pdfinvoice();
$filename = __DIR__.'/pdfdocs/TCINVOICE_'.$request['id'][0].'.PDF';
echo $filename;
$pdf->Output($filename, 'F');

function pdfinvoice() {
 global $pdf,$request,$copy;
 $firstline = 90;
 $lastline = 210;
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
  $pdf->Text(40,$ypos,ucwords(strtolower($doc[7][$i])));
  $num = round($doc[10][$i]);
  $w = $pdf->getStringWidth($num);
  $pdf->Text(144-$w,$ypos, $num);

  $num = round($doc[9][$i]/$doc[8][$i]+.49) ;
  list($w,$d) = numsizes($num);
  $pdf->Text(162-$w,$ypos,$d);
  $num = round($doc[9][$i]/$doc[8][$i]*$doc[10][$i]+.49);
  list($w,$d) = numsizes($num);
  $pdf->Text(185-$w,$ypos,$d);
  $pdf->Text(192,$ypos, $doc[16][$i]);
  
  if (substr($doc[6][$i],0,2) == 'S*') {
	$ypos = $ypos + 5;
	$pdf->Text(41,$ypos,'The above product is a special item and cannot be returned');
  }
  
  $ypos = $ypos + 5 ;
 }
 lastfooter();

}
exit;

function otherheader() {
 otherfooter();
 firstheader();
}

function otherfooter() {
 global $pdf,$request,$copy;
	$pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;
 $pdf->Text(160,275,"Continued on next page."); 
}

function firstheader() {
 global $pdf,$request,$copy;
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
 $pdf->Text(20,40,$request['customer'][0]);
 $pdf->Text(20,44,$request['customer'][1]);
 $pdf->Text(20,48,$request['customer'][2]);
 $pdf->Text(20,52,$request['customer'][3]);
 $pdf->Text(20,56,$request['customer'][4]);

 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 if ($doc[26] == 'CN' OR $doc[26] == 'CR') {
  $t = 'CREDIT NOTE';
 } else {
  $t = 'SALES INVOICE';
 }
 $t = $request['ordertype'][0];
	$pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetFont(null,'',13);
	$pdf->Text(145,29,'Invoice No.');
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
 $pdf->Text(40,85,'Description');
 $pdf->Text(130,85,'Quantity');
 $pdf->Text(157,85,'Price');
 $pdf->Text(171,85,'Line Total');
 $pdf->SetFont(null,'B',5);
 $pdf->Text(189.5,85.4,'VAT');
 $pdf->Text(189.5,87.3,'Code');


}

function lastfooter() {
 global $pdf,$request,$copy;

	$pdf->Line(15,250,195,250);
 $doc = $request['doc'];

 $pdf->SetFont(null,'',10);
 $pdf->Text(15,252,'Delivery Address');
 $pdf->Text(16,257,ucwords(strtolower(substr($doc[33][0],0,31))));
 $pdf->Text(16,261,ucwords(strtolower(substr($doc[33][1],0,31))));
 $pdf->Text(16,265,ucwords(strtolower(substr($doc[33][2],0,31))));
 $pdf->Text(16,269,ucwords(strtolower(substr($doc[33][3],0,31))));
 $pdf->Text(16,273,ucwords(strtolower(substr($doc[33][4],0,31))));

 
 $pdf->Text(15,287,'VAT Number:'.$request['company'][8].' e:admin@plumlink.co.uk '.' phone:'.$request['branch'][7]);


 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'N',13);
	$pdf->Text(154,252,'Sub total'); 
	$pdf->Text(154,257,'VAT '); 
	$pdf->Text(154,262,'Total');
 $pdf->SetAlpha(1);

 list($w,$d) = numsizes($doc[42]);
 $pdf->Text(190-$w,252,$d);

 list($w,$d) = numsizes($doc[41]);
 $pdf->Text(190-$w,257,$d);

 $pdf->SetAlpha(0.5);
 $pdf->SetAlpha(1);

 $pdf->SetFont(null,'',13);

 list($w,$d) = numsizes($doc[41]+$doc[42]);
 $pdf->Text(190-$w,262,$d);

 $pdf->SetFont(null,'',13);

 $pdf->Rect(150,220,45,30);
 $pdf->SetAlpha(.2);
 $pdf->Text(152,221,$copy." Copy");
 $pdf->Text(152,226,'Sign Here');
 $pdf->SetFont(null,'N',10); 

 $pdf->SetAlpha(1);

 $style = array('border'=>2,'vpadding'=>'auto','hpadding'=>'auto','fgcolor'=>array(0,0,0),'bgcolor'=>false,'module_width'=>1,'module_height'=>1);
 $pdf->write2DBarcode('DN'.$request['id'][0] , 'QRCODE,L', 15, 220, 30, 30, $style, 'N');

 $pdf->SetFont(null,'N',10); 


 $pdf->Text(70,252,'Pay by cheque to: ');
 $pdf->Text(71,257,ucwords(strtolower($request['company'][0])));
 $pdf->Text(71,261,ucwords(strtolower($request['company'][1])));
 $pdf->Text(71,265,ucwords(strtolower($request['company'][2])));
 $pdf->Text(71,269,ucwords(strtolower($request['company'][3])));
 $pdf->Text(71,273,$request['company'][4]);

// $pdf->Text(110,252,'Pay By Direct Credit To');
// $pdf->Text(111,257,'Bank: '.@$request['company'][9]);
// $pdf->Text(111,261,'Reference: '.$request['id'][0]);

}

?>
